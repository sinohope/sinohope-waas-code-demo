package common

import "encoding/json"

type Request struct {
	RequestType   int             `json:"requestType,omitempty"`
	RequestId     string          `json:"requestId,omitempty"`
	RequestDetail json.RawMessage `json:"requestDetail,omitempty"`
	ExtraInfo     json.RawMessage `json:"extraInfo,omitempty"`
}

type Response struct {
	Success   bool   `json:"success,omitempty"`
	Code      int    `json:"code,omitempty"`
	Message   string `json:"message,omitempty"`
	RequestId string `json:"requestId,omitempty"`
	Action    string `json:"action,omitempty"`
}
