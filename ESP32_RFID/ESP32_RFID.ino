#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>

#define SS_PIN 5    // SDA pin connected to GPIO 5 (D5)
#define RST_PIN 22  // RST pin connected to GPIO 22 (D22)

MFRC522 mfrc522(SS_PIN, RST_PIN);  // Create MFRC522 instance.

const char* ssid = "AndRex";
const char* password = "rat12345";
const char* device_token = "63c0f220553c49ef";
const char* server_address = "http://inventory.thecyberdelta.com/getdata.php";

HTTPClient http;  // Declare HTTPClient object here

void setup() {
  Serial.begin(115200);
  SPI.begin();         // Init SPI bus
  mfrc522.PCD_Init();  // Init MFRC522 card

  connectToWiFi();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();  // Retry to connect to Wi-Fi
  }

  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    String uid;
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      uid += String(mfrc522.uid.uidByte[i], HEX);
    }
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();

    // Create URL using String::format method
    char url[200];
    snprintf(url, sizeof(url), "%s/?card_uid=%s&device_token=%s", server_address, uid.c_str(), device_token);

    Serial.println(uid);
    

    HTTPClient http;
    int httpCode = http.begin(url);
    httpCode = http.GET();
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("Server Response: " + payload);
    } else {
      Serial.println("HTTP GET request failed");
    }
    http.end();

    delay(500);  // To avoid reading the same card multiple times
    //debug
    Serial.print("URL: ");
    Serial.println(url);

    Serial.print("HTTP Code: ");
    Serial.println(httpCode);

    Serial.print("HTTP GET Result: ");
    Serial.println(httpCode);
    //debug
  }
}

void connectToWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to ");
  Serial.println(ssid);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("Connected");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}
