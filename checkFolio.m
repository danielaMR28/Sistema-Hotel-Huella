function checkFolio()
    % Crear campo de texto y botón
    fig = uifigure;
    fig.Position = [500 500 300 200];
    
    % Campo de texto para ingresar folio
    folioEdit = uieditfield(fig, 'text');
    folioEdit.Position = [50 120 200 22];
    folioEdit.Placeholder = 'Ingrese folio';
    
    % Botón deshabilitado inicialmente
    activarBtn = uibutton(fig, 'push');
    activarBtn.Position = [100 50 100 30];
    activarBtn.Text = 'Registrar huella';
    activarBtn.Enable = 'off';
    
    % Función para verificar folio
    function verificarFolio(src, ~)
        folio = folioEdit.Value;
        
        % Leer archivo de texto
        fid = fopen('/Users/danielamejiarivas/Downloads/CRUD-with-docker-compose/shared/folio.txt', 'r');
        folioGuardado = fscanf(fid, '%s');
        fclose(fid);
        
        % Comparar folios
        if strcmp(folio, folioGuardado)
            activarBtn.Enable = 'on';
        else
            activarBtn.Enable = 'off';
            uialert(fig, 'Folio no válido', 'Error');
        end
    end

    % Asociar función de verificación al cambio de texto
    folioEdit.ValueChangedFcn = @verificarFolio;
    
    % Función para activar sensor (placeholder)
    function activarSensor(~, ~)
        % Aquí iría tu código para activar el sensor
        registrarHuellaGUI;
    end

    % Asociar función de activación al botón
    activarBtn.ButtonPushedFcn = @activarSensor;
end