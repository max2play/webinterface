//@Author Stefan Rick Max2Play

//build with gcc -I. -o pwrbutton pwrbutton.c -lwiringPi
#include <stdio.h>
#include <string.h>
#include <errno.h>
#include <stdlib.h>
#include <sys/time.h>
#include <wiringPi.h>

typedef enum { false, true } bool;
static bool globalActive = false;
static int globalCounter = 0;
struct timeval t1_light,t2;

// Set WiringPi GPIO
int gpiopin = 0;

int showElapsedTime(void) {
    static int t_delay,seconds,useconds;
    gettimeofday(&t2, NULL);
    seconds  = t2.tv_sec  - t1_light.tv_sec;
    useconds = t2.tv_usec - t1_light.tv_usec;
    if(useconds < 0) {
      useconds += 1000000;
      seconds--;
    }
    printf("start/end: %d sek %d msek\n\n",seconds,useconds/1000);
    return seconds;
}

void myInterrupt(void) {
  int pin = digitalRead(gpiopin);
  if(pin == LOW && globalActive == false){
    globalActive = true;
    printf ("Button pressed\n");
    gettimeofday(&t1_light, NULL);
  }else if(pin == HIGH && globalActive == true){
    printf ("Button Released\n");
    int seconds = showElapsedTime();
    int sysreturn = 0;
    char command[100];
    sprintf(command ,"/opt/max2play/pwrbutton.sh %d", seconds);
    sysreturn = system(command);
    printf("System: %d", sysreturn);
    globalActive = false;
  }
}

int main (void)
{
  wiringPiSetup () ;

  wiringPiISR (gpiopin, INT_EDGE_BOTH, &myInterrupt) ;

  for (;;) {
    sleep(1000);
  }
  return 0 ;
}
