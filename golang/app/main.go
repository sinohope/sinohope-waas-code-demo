package main

import (
	"flag"
	"fmt"
	"os"
	"os/signal"
	"syscall"

	"github.com/gin-gonic/gin"
	"github.com/sinohope/sinohope-waas-code-demo/common"
	"github.com/sinohope/sinohope-waas-code-demo/handlers"
	"github.com/sinohope/sinohope-waas-code-demo/log"
	"github.com/sirupsen/logrus"
)

var (
	version = flag.Bool("version", false, "show version")
	address = flag.String("address", "127.0.0.1:8080", "Address of call back server")
)

func main() {
	flag.Parse()

	if *version {
		fmt.Printf("\x1b[%dm%s\x1b[0m %s\n", common.Blue, "tag:       ", common.Tag)
		fmt.Printf("\x1b[%dm%s\x1b[0m %s\n", common.Blue, "commit:    ", common.Commit)
		fmt.Printf("\x1b[%dm%s\x1b[0m %s\n", common.Blue, "build time:", common.BuildTime)
		return
	}

	log.SetLogDetailsByConfig("callback-demo", log.Config{
		Stdout: log.Stdout{
			Enable: true,
			Level:  5,
		},
		File: log.File{
			Enable: true,
			Level:  5,
			Path:   "./logs/callback-demo.log",
		},
	})
	logrus.Infof("tag: [%v]", common.Tag)
	logrus.Infof("commit: [%v]", common.Commit)
	logrus.Infof("build time: [%v]", common.BuildTime)
	logrus.
		WithField("version", *version).
		WithField("address", *address).
		Debugf("callback-demo init")

	r := gin.Default()
	r.Any("/*any", handlers.Any)

	logrus.Infof("prepare to start callback-demo at %v...", *address)
	if err := r.Run(*address); err != nil {
		logrus.Panic(err)
	}

	quit := make(chan os.Signal)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM, syscall.SIGQUIT)
	<-quit
	logrus.Infof("callback-demo quit")
}
