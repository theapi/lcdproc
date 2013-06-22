#!/usr/bin/python
# -*- coding: utf-8 -*-

import socket, select, atexit

from time import sleep
from Adafruit_CharLCDPlate import Adafruit_CharLCDPlate


#Function to received data
def handle_data (sock, message):
    
    # Find which function is wanted
    if message[:8] == 'message:':
        lcd.clear()
        lcd.message(message[8:])

    elif message[:10] == 'backlight:':
        backlight = message[10:].strip()
        # print backlight
        if backlight == '0':
            #print "   backlight: OFF"
            lcd.backlight(lcd.OFF)
        elif backlight == '1':
            #print "   backlight: ON"
            lcd.backlight(lcd.ON)
        elif backlight == 'red':
            lcd.backlight(lcd.RED)
        elif backlight == 'green':
            lcd.backlight(lcd.GREEN)
        elif backlight == 'blue':
            lcd.backlight(lcd.BLUE)
        elif backlight == 'yellow':
            lcd.backlight(lcd.YELLOW)
        elif backlight == 'teal':
            lcd.backlight(lcd.TEAL)
        elif backlight == 'violet':
            lcd.backlight(lcd.VIOLET)       
        elif backlight == 'white':
            lcd.backlight(lcd.WHITE)         

    try:
        sock.send("ok\n")
    except :
        # broken socket connection may be, chat client pressed ctrl+c for example
        sock.close()
        CONNECTION_LIST.remove(socket)

def goodbye():
    lcd.clear()
    lcd.backlight(lcd.VIOLET)
    lcd.message("BYE")
    sleep(.2)
    lcd.backlight(lcd.OFF)
    lcd.clear()
    lcd.stop() 

atexit.register(goodbye)

if __name__ == "__main__":
    
    # Initialize the LCD plate.  Should auto-detect correct I2C bus.  If not,
    # pass '0' for early 256 MB Model B boards or '1' for all later versions
    lcd = Adafruit_CharLCDPlate()
    
    # Clear display and show greeting, pause 1 sec
    lcd.clear()
    lcd.message("Adafruit RGB LCD\nPlate w/Keypad!")

    print "❤"     
    # List to keep track of socket descriptors
    CONNECTION_LIST = []
    RECV_BUFFER = 4096 # Advisable to keep it as an exponent of 2
    PORT = 8888
     
    server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    # this has no effect, why ?
    server_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server_socket.bind(("0.0.0.0", PORT))
    server_socket.listen(1)
 
    # Add server socket to the list of readable connections
    CONNECTION_LIST.append(server_socket)
 
    print "Server started on port " + str(PORT)
    lcd.clear()
    # no fancy utf8 like: lcd.message("❤")
    lcd.message("Started on\nport " + str(PORT))
    #lcd.write(u"\u2764"); 
    while 1:
        # Get the list sockets which are ready to be read through select
        read_sockets,write_sockets,error_sockets = select.select(CONNECTION_LIST,[],[],0.1)
 
        for sock in read_sockets:
            #New connection
            if sock == server_socket:
                # Handle the case in which there is a new connection recieved through server_socket
                sockfd, addr = server_socket.accept()
                CONNECTION_LIST.append(sockfd)
                print "Client (%s, %s) connected" % addr
                 
                #broadcast_data(sockfd, "%s:\n%s" % addr)
             
            #Some incoming message from a client
            else:
                # Data recieved from client, process it
                try:
                    #In Windows, sometimes when a TCP program closes abruptly,
                    # a "Connection reset by peer" exception will be thrown
                    data = sock.recv(RECV_BUFFER)
                    if data:
                        #data = data.decode("utf-8")
                        handle_data(sock, data)                
                 
                except:
                    print "Client (%s, %s) is offline" % addr
                    sock.close()
                    CONNECTION_LIST.remove(sock)
                    continue
     
    server_socket.close()




# Clear the screen
lcd.clear()
lcd.backlight(lcd.OFF)
