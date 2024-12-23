package verify

import (
	"encoding/hex"
	"sort"
	"strings"

	"github.com/sirupsen/logrus"

	"github.com/sinohope/sinohope-waas-code-demo/verify/crypto"
)

func Verify(path, key, nonce, signature, body string) (bool, error) {
	message := request2Message(key, path, nonce, body)
	return crypto.Verify(key, message, signature)
}

func request2Message(key, path, timestamp, payload string) string {
	data := map[string]string{
		"data":      payload,
		"path":      path,
		"timestamp": timestamp,
		"version":   "1.0.0",
	}
	keys := make([]string, 0, len(data))
	for k := range data {
		keys = append(keys, k)
	}
	sort.Strings(keys)
	signature := ""
	for _, k := range keys {
		signature += k + data[k]
	}
	signature += key
	signature = strings.ReplaceAll(signature, "\n", "")
	signature = strings.ReplaceAll(signature, " ", "")
	message := hex.EncodeToString([]byte(signature))
	logrus.Infof("message to be verify, %v", message)
	return message
}
