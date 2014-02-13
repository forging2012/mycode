#include <stdio.h>
#include <stdlib.h>
#include <pthread.h>

pthread_t ntid;

//打印线程信息
void printids(const char *s)
{
    pid_t       pid;
    pthread_t   tid;
    
    pid = getpid();
    tid = pthread_self();
    printf("%s pid %u tid %u (0x%x)\n", s, (unsigned int)pid,
        (unsigned int)tid, (unsigned int)tid);
}
//线程执行的方法
void * thr_fn(void *arg)
{
    printids("new thread: ");
    return((void *)0);
}

int main(void)
{
    int err;
    err = pthread_create(&ntid, NULL, thr_fn, NULL);
    if (err != 0)
        printf("can't create thread: %s\n", strerror(err));
    printids("main thread:");
    sleep(1);
    exit(0);
}
