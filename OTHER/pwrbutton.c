#include <linux/input.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <fcntl.h>
#include <stdio.h>
#include <unistd.h>

typedef enum { false, true } bool;
struct timeval t1,t2;

int showElapsedTime(struct timeval t2) {
    static int t_delay,seconds,useconds;
    seconds  = t2.tv_sec  - t1.tv_sec;
    useconds = t2.tv_usec - t1.tv_usec;
    if(useconds < 0) {
      useconds += 1000000;
      seconds--;
    }
    //printf("start/end: %d sek %d msek\n\n",seconds,useconds/1000);
    return seconds;
}

int main(int argc, char **argv)
{
    int fd;
    char * script = "/opt/max2play/pwrbutton.sh";
    char out [100];
    
    if(argc > 1) {
        if(strcmp(argv[1], "--help") == 0){
        	printf("This Programm monitors the power-button and starts a script with a parameter that offers the time between press and release in seconds. \n");
        	printf("Usage:  \n");
        	printf("  without any parameters this script calls %s  \n", script);
        	printf("  --help shows this help   \n");
        	printf("  modify the script location by passing it as first argument  \n");
        	return 1;
        } else {
            script = argv[1];
            //TODO check if script exists            
        }	
    }
    fd = open("/dev/input/event0", O_RDONLY);
    struct input_event ev;

    while (1)
    {
        read(fd, &ev, sizeof(struct input_event));

        if(ev.type == 1 && ev.code == 116){
            //Key = 116, value=1 press, value=0 release
	        //printf("key %i state %i\n", ev.code, ev.value);
            if(ev.value == 0){
                //printf("Release %i\n ", ev.code);
                int seconds = showElapsedTime(ev.time);
                //printf("Seconds %i", seconds);
	            int sysreturn = 0;	        
		        //Button Press Script
		        sprintf(out, "%s %d", script, seconds);
		        sysreturn = system(out);
	        }
		    if(ev.value == 1){
		        //printf("Press %i - %ld", ev.code, ev.time.tv_sec);
                t1=ev.time;
		    }
		}    
	}
}

