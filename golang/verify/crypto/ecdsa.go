package crypto

import (
	"crypto/ecdsa"
	"crypto/sha256"
	"crypto/x509"
	"encoding/hex"

	"github.com/sirupsen/logrus"
)

func Verify(public, message, signature string) (bool, error) {
	logrus.
		WithField("public", public).
		WithField("message-to-verify", message).
		WithField("signature", signature).
		Debugf("prepare to verify")
	publicKeyBytes, err := hex.DecodeString(public)
	if err != nil {
		return false, err
	}
	pubInterface, err := x509.ParsePKIXPublicKey(publicKeyBytes)
	if err != nil {
		return false, err
	}
	pub, ok := pubInterface.(*ecdsa.PublicKey)
	if !ok {
		return false, err
	}
	signatureBytes, err := hex.DecodeString(signature)
	if err != nil {
		return false, err
	}
	messageBytes, err := hex.DecodeString(message)
	if err != nil {
		return false, err
	}
	hash := sha256.Sum256(messageBytes)
	return ecdsa.VerifyASN1(pub, hash[:], signatureBytes), nil
}
