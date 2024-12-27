package handlers

import (
	"fmt"
	"io/ioutil"
	"net/http"

	golang "github.com/sinohope/sinohope-waas-code-demo/verify"

	"github.com/gin-gonic/gin"
	"github.com/sirupsen/logrus"
)

func process(g *gin.Context) {
	apiKey := g.Request.Header.Get("BIZ-API-KEY")
	apiSignature := g.Request.Header.Get("BIZ-API-SIGNATURE")
	apiNonce := g.Request.Header.Get("BIZ-API-NONCE")
	logrus.
		WithField("BIZ-API-KEY", apiKey).
		WithField("BIZ-API-SIGNATURE", apiSignature).
		WithField("BIZ-API-NONCE", apiNonce).
		Infof("request header info")
	logrus.
		WithField("path", g.Request.URL.Path).
		Infof("request path")
	body, err := ioutil.ReadAll(g.Request.Body)
	if err != nil {
		g.JSON(http.StatusBadGateway, gin.H{
			"success": false,
			"code":    1005,
			"msg":     "failed to read request body, reason: " + err.Error(),
		})
		return
	}
	logrus.
		WithField("body", string(body)).
		Infof("request body")
	if ok, err := golang.Verify(g.Request.URL.Path, apiKey, apiNonce, apiSignature, string(body)); !ok {
		var msg string
		if err != nil {
			msg = fmt.Sprintf("verify request failed, %v", err)
		} else {
			msg = fmt.Sprintf("verify request failed")
		}
		logrus.Errorf("%v", msg)
		g.JSON(http.StatusUnauthorized, gin.H{
			"success": false,
			"code":    1005,
			"msg":     msg,
		})
		return
	}
	logrus.Infof("verify request success")
	action := ""
	switch g.Request.URL.Path {
	case "/v1/call_back/withdrawal/confirm":
		action = "APPROVE"
		logrus.
			WithField("action", action).
			WithField("path", g.Request.URL.Path).
			Infof("approve withdrawal")
	}
	g.JSON(http.StatusOK, gin.H{
		"success": true,
		"code":    200,
		"action":  action,
	})
}
