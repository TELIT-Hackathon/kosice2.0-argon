#include <WiFi.h>
#include <HTTPClient.h>
#include "FS.h"
#include "SPIFFS.h"

/* You only need to format SPIFFS the first time you run a
   test or else use the SPIFFS plugin to create a partition
   https://github.com/me-no-dev/arduino-esp32fs-plugin */
#define FORMAT_SPIFFS_IF_FAILED true

/*const char* ssid = "HACKATHON_2022 WiFi";     // your network SSID (name of wifi network)
const char* password = "H4CK4TH0N2022"; // your network password*/
 
const char* ssid = "Argon";     // your network SSID (name of wifi network)
const char* password = "argonargon"; // your network password
int LED_inbuilt = 33;

#define GCODE_DELAY 010

void setup() {
  pinMode(4, OUTPUT);
  Serial.end();
  delay(100);
  Serial.begin(115200);
  pinMode(LED_inbuilt, OUTPUT);
  delay(1000);
  Serial.print(";WIFI status = ");
  Serial.println(WiFi.getMode());
  WiFi.disconnect(true);

  delay(1000);
  WiFi.mode(WIFI_STA);
  delay(1000);
  Serial.print(";WIFI status = ");
  Serial.println(WiFi.getMode());
  
  digitalWrite(LED_inbuilt, HIGH);
  delay(500);
  digitalWrite(LED_inbuilt, LOW);
  delay(500);
 
  WiFi.begin(ssid, password); 
 int attempt = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println(";Connecting to WiFi..");
    attempt++;
    if(attempt > 50){
      digitalWrite(4, HIGH);
      delay(50);
      digitalWrite(4, LOW);
      delay(100);
      digitalWrite(4, HIGH);
      delay(50);
      digitalWrite(4, LOW);
      delay(100);
      digitalWrite(4, HIGH);
      delay(50);
      digitalWrite(4, LOW);
      delay(100);
      ESP.restart();
    }
  }
 
  Serial.println(";Connected to the WiFi network");
  
  if (!SPIFFS.begin(FORMAT_SPIFFS_IF_FAILED)) {
    Serial.println(";SPIFFS Mount Failed");
    return;
  }

  deleteFile(SPIFFS, "/graph.gcode");
} 
 
void loop() {
  while(1){
    digitalWrite(4, LOW);
    if(!getFile()){
      deleteFile(SPIFFS, "/graph.gcode");
      digitalWrite(4, HIGH);
      delay(30);
       break;
    }
    //readFile(SPIFFS, "/graph.gcode");
    uartPuffer(SPIFFS, "/graph.gcode");
    delay(5000);
    deleteFile(SPIFFS, "/graph.gcode");
  }
}

bool getFile() {
 File file = SPIFFS.open("/graph.gcode", "a");

 if (!file) {
   Serial.println(";- failed to open file for writing");
   return 0;
 }

 if ((WiFi.status() == WL_CONNECTED)) { // Check the current connection status
   HTTPClient http;
   http.begin("https://firstimeke.tk/maps/graph.gcode"); //Specify the URL and certificate
    int httpCode = http.GET(); // Make the request
 
    if (httpCode == HTTP_CODE_OK) { // Check for the returning code
      http.writeToStream(&file);
    }else{
      Serial.println(";ERROR 404!");
      return 0;
    }
    
    file.close();
    Serial.println(";you have finished downloading");
    http.end(); // Free the resources
  }
  return 1;
}

void readFile(fs::FS &fs, const char * path) {
  Serial.printf("Reading file: %s\r\n", path);

  File file = fs.open(path);
  if (!file || file.isDirectory()) {
    Serial.println(";- failed to open file for reading");
    return;
  }

  Serial.println(";- read from file:");

  while (file.available()) {
    Serial.write(file.read());
  }
}

void uartPuffer(fs::FS &fs, const char * path) {

  File file = fs.open(path);
  if (!file || file.isDirectory()) {
    return;
  }
  int delay_s = file.read();
  delay_s = delay_s - 34;
  char buf = 0;
  while (file.available()) {
    buf = file.read();
    if(buf == '*'){
      delay(delay_s);
      buf = file.read();
      Serial.write(10);
    }else if(buf != 10){
    Serial.write(buf);
    }
  }
}

void deleteFile(fs::FS &fs, const char * path) {
  Serial.printf(";Deleting file: %s\r\n", path);
  if (fs.remove(path)) {
    //Serial.println("- file deleted");
  } else {
    //Serial.println("- delete failed");
  }
}
