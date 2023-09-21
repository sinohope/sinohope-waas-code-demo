package handlers

import (
	"github.com/gin-gonic/gin"
)

// TransactionNotify ...
// POST: /v1/call_back/transaction/notify
func TransactionNotify(g *gin.Context) {
	process(g)
}
