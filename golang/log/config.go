package log

type Config struct {
	Stdout `toml:"stdout"`
	File   `toml:"file"`
}

type Stdout struct {
	Enable bool `toml:"enable"`
	Level  int  `toml:"level"`
}
type File struct {
	Enable bool   `toml:"enable"`
	Level  int    `toml:"level"`
	Path   string `toml:"path"`
	MaxAge int    `toml:"max-age"`
}
