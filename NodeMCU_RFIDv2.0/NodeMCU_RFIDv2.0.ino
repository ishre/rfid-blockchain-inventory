#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 21    // SDA pin connected to D21
#define RST_PIN 22   // RST pin connected to D22

MFRC522 mfrc522(SS_PIN, RST_PIN);   // Create MFRC522 instance.

// Function declarations
void readCardData();
void writeCardData();

void setup() {
  Serial.begin(115200);
  SPI.begin();      // Init SPI bus
  mfrc522.PCD_Init();   // Init MFRC522 card

  Serial.println("Place your NFC card near the reader...");
  Serial.println("Type 'r' to read, 'w' to write, or 'q' to quit.");
}

void loop() {
  if (Serial.available() > 0) {
    String input = Serial.readStringUntil('\n'); // Read the entire line until newline character

    char command = input.charAt(0); // Extract the first character

    switch (command) {
      case 'r':
      case 'R':
        readCardData();
        break;
      case 'w':
      case 'W':
        writeCardData();
        break;
      case 'q':
      case 'Q':
        Serial.println("Quitting...");
        delay(1000);
        return;
      default:
        Serial.println("Invalid command. Please type 'r', 'w', or 'q'.");
        break;
    }
  }
}

void readCardData() {
  // Same code as before
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    Serial.println("A card is detected!");

    // Show card details (UID)
    Serial.print("Card UID: ");
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      Serial.print(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ");
      Serial.print(mfrc522.uid.uidByte[i], HEX);
    }
    Serial.println();

    // Halt PICC and stop encryption
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
  }
}

void writeCardData() {
  Serial.println("Please place a card to write data...");
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    Serial.println("Card detected! Writing data...");

    // Authenticate using the default key and block address
    MFRC522::MIFARE_Key key;
    for (byte i = 0; i < 6; i++) {
      key.keyByte[i] = 0xFF;
    }
    byte blockAddr = 4;   // Choose a block (0-63) where you want to write data

    // Prepare data to write (16 bytes)
    byte data[] = { 'H', 'e', 'l', 'l', 'o', ' ', 'N', 'F', 'C', '!', '!', '!', '!', '!', '!', '!' };

    // Authenticate and write data to the block
    MFRC522::StatusCode status = mfrc522.PCD_Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, blockAddr, &key, &(mfrc522.uid));
    if (status != MFRC522::STATUS_OK) {
      Serial.println("Authentication failed!");
    } else {
      status = mfrc522.MIFARE_Write(blockAddr, data, 16);
      if (status != MFRC522::STATUS_OK) {
        Serial.println("Writing data failed!");
      } else {
        Serial.println("Data successfully written!");
      }
      // Halt PICC and stop encryption
      mfrc522.PICC_HaltA();
      mfrc522.PCD_StopCrypto1();
    }
  }
}
