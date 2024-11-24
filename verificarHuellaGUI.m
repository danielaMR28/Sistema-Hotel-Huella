function verificarHuellaGUI()
    % Crear la figura principal con un diseño más moderno
    f = figure('Name', 'Verificación de Huella Digital', ...
               'Position', [300, 300, 400, 500], ...
               'Color', [0.95 0.95 0.95], ...
               'MenuBar', 'none', ...
               'NumberTitle', 'off');
    
    % Panel principal
    mainPanel = uipanel('Parent', f, ...
                       'Position', [0.05 0.05 0.9 0.9], ...
                       'BackgroundColor', 'white', ...
                       'BorderType', 'none');
    
    % Título
    uicontrol('Parent', mainPanel, ...
              'Style', 'text', ...
              'String', 'Verificación de Huella Digital', ...
              'Position', [50, 400, 300, 40], ...
              'FontSize', 16, ...
              'FontWeight', 'bold', ...
              'BackgroundColor', 'white');
    
    % Imagen representativa
    ax = axes('Parent', mainPanel, ...
             'Position', [0.25 0.45 0.5 0.4]);
    try
        % Intenta cargar la imagen (reemplaza 'huella.png' con tu archivo de imagen)
        img = imread('fingerprint-8.png');
        imshow(img);
    catch
        % Si no se puede cargar la imagen, muestra un mensaje de error
        text(0.5, 0.5, 'Imagen no encontrada', ...
             'HorizontalAlignment', 'center', ...
             'Color', 'red');
    end
    axis off;
    
    % Botón de verificación con diseño mejorado
    uicontrol('Parent', mainPanel, ...
              'Style', 'pushbutton', ...
              'String', 'Verificar Huella', ...
              'Position', [75, 150, 250, 40], ...
              'BackgroundColor', [0.4 0.7 0.4], ...
              'ForegroundColor', 'white', ...
              'FontSize', 12, ...
              'Callback', @(src,event)enviarComando('verificar'));
    
    % Campo de estado con mejor diseño
    global mensajeText;
    mensajeText = uicontrol('Parent', mainPanel, ...
                           'Style', 'text', ...
                           'String', 'Esperando acción...', ...
                           'Position', [50, 80, 300, 30], ...
                           'BackgroundColor', 'white', ...
                           'FontSize', 10);
    
    % Inicializar conexión serial
    global arduino;
    arduino = [];
    try
        arduino = serialport("COM4", 9600);
        configureCallback(arduino, "terminator", @recibirDatos);
        set(mensajeText, 'String', 'Conectado a Arduino');
    catch
        set(mensajeText, 'String', 'Error de conexión');
    end
end

function enviarComando(comando)
    global arduino mensajeText;
    if ~isempty(arduino)
        writeline(arduino, comando);
        set(mensajeText, 'String', ['Enviando comando: ' comando]);
    else
        set(mensajeText, 'String', 'Arduino no conectado');
    end
end

function recibirDatos(src, ~)
    global mensajeText;
    datos = readline(src);
    set(mensajeText, 'String', datos);
end