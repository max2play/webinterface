/**
 * discover_squeezebox_server.c 
 * Stefan Rick Max2Play.com
 * Compiled with gcc -o discover_squeezebox_server discover_squeezebox_server.c 
 */


#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <sys/select.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <poll.h>

#define PORT 3483

int main(int argc, char *argv[]) {
	printf("Get IP of Squeezebox Server in Network\n");
	printf("Usage: %s [-d]\n", argv[0]);
	printf(" -d Enable DEBUG output\n\n");
	
	struct sockaddr_in d;
	struct sockaddr_in s;
	char *buf;
	struct pollfd pollinfo;

	int disc_sock = socket(AF_INET, SOCK_DGRAM, 0);

	socklen_t enable = 1;
	setsockopt(disc_sock, SOL_SOCKET, SO_BROADCAST, (const void *)&enable, sizeof(enable));

	buf = "e";

	memset(&d, 0, sizeof(d));
	d.sin_family = AF_INET;
	d.sin_port = htons(PORT);
	d.sin_addr.s_addr = htonl(INADDR_BROADCAST);

	pollinfo.fd = disc_sock;
	pollinfo.events = POLLIN;
    
    int test = 0;
    
	do {
 	    test++; 		
		memset(&s, 0, sizeof(s));

		if (sendto(disc_sock, buf, 1, 0, (struct sockaddr *)&d, sizeof(d)) < 0) {
			printf("ERROR sending disovery\n");
		}

		if (poll(&pollinfo, 1, 5000) == 1) {
			char readbuf[10];
			socklen_t slen = sizeof(s);
			recvfrom(disc_sock, readbuf, 10, 0, (struct sockaddr *)&s, &slen);
			printf("SERVER:%s\n", inet_ntoa(s.sin_addr));
		}

	} while (s.sin_addr.s_addr == 0 && test < 3);

	close(disc_sock);

}
