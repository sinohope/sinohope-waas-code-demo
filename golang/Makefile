tag := $(shell git describe --tags --exact-match 2>/dev/null || echo "")
commit := $(shell git rev-parse HEAD)
build_time := $(shell date -u +"%Y-%m-%dT%H:%M:%SZ")
MODULE = "github.com/sinohope/sinohope-waas-code-demo"

LD_FLAGS := -ldflags "-w -s \
	-X github.com/sinohope/sinohope-waas-code-demo/common.Tag=${tag} \
	-X github.com/sinohope/sinohope-waas-code-demo/common.Commit=${commit} \
	-X github.com/sinohope/sinohope-waas-code-demo/common.BuildTime=${build_time}"
CROSS_COMPILE = CGO_ENABLED=0 GOOS=linux GOARCH=amd64
CILINT := $(shell command -v golangci-lint 2> /dev/null)
GOIMPORTS := $(shell command -v goimports 2> /dev/null)

style:
ifndef GOIMPORTS
	$(error "goimports is not available please install goimports")
endif
	! find . -path ./vendor -prune -o -name '*.go' -print | xargs goimports -d -local ${MODULE} | grep '^'

format:
ifndef GOIMPORTS
	$(error "goimports is not available please install goimports")
endif
	find . -path ./vendor -prune -o -name '*.go' -print | xargs goimports -l -local ${MODULE} | xargs goimports -l -local ${MODULE} -w

clean:
	rm -fr ./build

linux_amd64:
	GOOS=linux GOARCH=amd64 go build -v -a ${LD_FLAGS} -o ./build/linux/amd64/callback-demo app/main.go
linux_arm64:
	GOOS=linux GOARCH=arm64 go build -v -a ${LD_FLAGS} -o ./build/linux/arm64/callback-demo app/main.go
linux: clean linux_amd64 linux_arm64

build: clean
	go build -v -a ${LD_FLAGS} -o ./build/callback-demo app/main.go

.PHONY: style format clean build linux linux_amd64 linux_arm64
