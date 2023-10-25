#include <stdio.h>
#include <unistd.h>

int main(void) {
  pid_t pid = fork();
  printf("PID: %d \n", pid);
}