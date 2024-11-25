#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>

SoftwareSerial mySerial(2, 3);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);

const int ledVerde = 8;
const int ledRojo = 9;

void setup() {
  Serial.begin(9600);
  while (!Serial);
  delay(100);
  
  finger.begin(57600);
  pinMode(ledVerde, OUTPUT);
  pinMode(ledRojo, OUTPUT);
  digitalWrite(ledVerde, LOW);
  digitalWrite(ledRojo, LOW);
  
  if (finger.verifyPassword()) {
    Serial.println("Sensor de huella encontrado!");
    finger.getTemplateCount();
    Serial.print("Huellas almacenadas: "); 
    Serial.println(finger.templateCount);
  } else {
    Serial.println("No se encontró el sensor de huella");
    while (1) { delay(1); }
  }
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();

    if (command == "registrar") {
      registrarHuella();
    } else if (command == "verificar") {
      verificarHuella();
    }
  }
}

uint8_t getFingerprintEnroll(uint8_t id) {
  int p = -1;
  Serial.println("Esperando dedo para registrar...");
  
  // Primera lectura
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    switch (p) {
      case FINGERPRINT_OK:
        Serial.println("Imagen capturada");
        break;
      case FINGERPRINT_NOFINGER:
        Serial.print(".");
        delay(100);
        break;
      case FINGERPRINT_PACKETRECIEVEERR:
        Serial.println("Error de comunicación");
        break;
      case FINGERPRINT_IMAGEFAIL:
        Serial.println("Error de imagen");
        break;
      default:
        Serial.println("Error desconocido");
        break;
    }
  }

  // Conversión primera imagen
  p = finger.image2Tz(1);
  if (p != FINGERPRINT_OK) {
    Serial.println("Error al procesar la imagen");
    return p;
  }

  Serial.println("Retire el dedo");
  delay(2000);
  p = 0;
  while (p != FINGERPRINT_NOFINGER) {
    p = finger.getImage();
  }

  Serial.println("Vuelva a colocar el mismo dedo");
  p = -1;
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    switch (p) {
      case FINGERPRINT_OK:
        Serial.println("Imagen capturada");
        break;
      case FINGERPRINT_NOFINGER:
        Serial.print(".");
        delay(100);
        break;
      case FINGERPRINT_PACKETRECIEVEERR:
        Serial.println("Error de comunicación");
        break;
      case FINGERPRINT_IMAGEFAIL:
        Serial.println("Error de imagen");
        break;
      default:
        Serial.println("Error desconocido");
        break;
    }
  }

  // Conversión segunda imagen
  p = finger.image2Tz(2);
  if (p != FINGERPRINT_OK) {
    Serial.println("Error al procesar la imagen");
    return p;
  }

  // Crear modelo
  Serial.println("Creando modelo de huella...");
  p = finger.createModel();
  
  // Aquí hacemos más tolerante la comparación
  if (p == FINGERPRINT_OK || finger.confidence > 50) {
    Serial.println("¡Las huellas coinciden!");
  } else if (p == FINGERPRINT_ENROLLMISMATCH) {
    Serial.println("Las huellas no coinciden. Intente nuevamente.");
    digitalWrite(ledRojo, HIGH);
    delay(2000);
    digitalWrite(ledRojo, LOW);
    return p;
  } else {
    Serial.println("Error en el proceso");
    return p;
  }

  p = finger.storeModel(id);
  if (p == FINGERPRINT_OK) {
    Serial.println("¡Huella guardada exitosamente!");
    digitalWrite(ledVerde, HIGH);
    delay(2000);
    digitalWrite(ledVerde, LOW);
  } else {
    Serial.println("Error al guardar la huella");
    digitalWrite(ledRojo, HIGH);
    delay(2000);
    digitalWrite(ledRojo, LOW);
    return p;
  }

  return true;
}

void registrarHuella() {
  Serial.println("Iniciando registro de huella...");
  int id = finger.templateCount + 1;
  if (id > 127) {
    Serial.println("Memoria llena. Límite de 127 huellas alcanzado.");
    return;
  }
  Serial.print("Registrando huella #"); 
  Serial.println(id);
  
  while (!getFingerprintEnroll(id));
}

void verificarHuella() {
  Serial.println("Esperando dedo para verificar...");
  
  unsigned long startTime = millis();
  bool fingerDetected = false;
  
  // Esperar hasta 10 segundos por un dedo
  while (millis() - startTime < 10000 && !fingerDetected) {
    uint8_t p = finger.getImage();
    switch (p) {
      case FINGERPRINT_OK:
        fingerDetected = true;
        Serial.println("Imagen tomada");
        break;
      case FINGERPRINT_NOFINGER:
        Serial.print(".");
        delay(200);
        break;
      case FINGERPRINT_PACKETRECIEVEERR:
        Serial.println("Error de comunicación");
        return;
      case FINGERPRINT_IMAGEFAIL:
        Serial.println("Error de imagen");
        return;
    }
  }
  
  if (!fingerDetected) {
    Serial.println("\nTiempo de espera agotado");
    return;
  }

  // Procesar la imagen
  uint8_t p = finger.image2Tz();
  if (p != FINGERPRINT_OK) {
    Serial.println("Error al convertir la imagen");
    return;
  }

  // Buscar coincidencia
  p = finger.fingerSearch();
  if (p == FINGERPRINT_OK) {
    Serial.print("Huella encontrada con ID #"); 
    Serial.print(finger.fingerID);
    Serial.print(" con confianza de "); 
    Serial.println(finger.confidence);
    digitalWrite(ledVerde, HIGH);
    delay(2000);
    digitalWrite(ledVerde, LOW);
  } else if (p == FINGERPRINT_NOTFOUND) {
    Serial.println("No se encontró coincidencia");
    digitalWrite(ledRojo, HIGH);
    delay(2000);
    digitalWrite(ledRojo, LOW);
  }
}
