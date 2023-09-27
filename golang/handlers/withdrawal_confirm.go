package handlers

import (
	"github.com/gin-gonic/gin"
)

// WithdrawalConfirm ...
// POST: /v1/call_back/withdrawal/confirm
func WithdrawalConfirm(g *gin.Context) {
	process(g)
}
