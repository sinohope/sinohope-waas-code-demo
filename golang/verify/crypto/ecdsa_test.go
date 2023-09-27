package crypto

import (
	"encoding/hex"
	"sort"
	"testing"

	"github.com/sirupsen/logrus"
)

func TestVerify(t *testing.T) {
	rawMessage := request2Message(
		"3059301306072a8648ce3d020106082a8648ce3d03010703420004bab316e744cc290e826dc47ac4c74764b657917edd1906475a792ede9e74d6e8f1793d05d6dbfac212f80b02101517c7f5484fc5d4fa1c43017a20cf97362255",
		"/v1/call_back/withdrawal/confirm",
		"1695807159732",
		"{\"requestId\":\"465594843472069\",\"requestDetail\":{\"sinoId\":\"463477224553413\",\"walletId\":\"459843560509957\",\"chainSymbol\":\"SEPOLIA\",\"assetId\":\"ETH_SEPOLIA\",\"from\":\"0xc96869f2c69126398b4e0f78b25c7721d502add7\",\"to\":\"0xc96869f2c69126398b4e0f78b25c7721d502add7\",\"note\":\"用户交易信息备注\",\"toTag\":\"32143\",\"amount\":\"97545733000000000\",\"decimal\":18,\"fee\":\"231000000000000\",\"gasLimit\":\"21000\",\"gasPrice\":\"11000000000\"},\"extraInfo\":\"\"}")
	public := "3059301306072a8648ce3d020106082a8648ce3d03010703420004bab316e744cc290e826dc47ac4c74764b657917edd1906475a792ede9e74d6e8f1793d05d6dbfac212f80b02101517c7f5484fc5d4fa1c43017a20cf97362255"
	signature := "3045022100da810356c8b59b5050b563361b9ba5ed3ddf18055b66f3ebad7e76b002c5336002207820edef1e092b2a35621ca27da9cea942cf5d8bd141cd22fc299a6d3cf82bf8"
	if ok, err := Verify(public, rawMessage, signature); !ok {
		t.Errorf("verify failed, %v", err)
	} else {
		t.Logf("verify success")
	}
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
	message := hex.EncodeToString([]byte(signature))
	logrus.Infof("message to be verify, %v", message)
	return message
}
