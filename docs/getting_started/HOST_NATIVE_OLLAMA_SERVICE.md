# Getting Started on dev with host native ollama service

In some cases (example: when on **macOS**) it's not possible to use the GPU within a docker container. 
To stay highly performant `ollama` needs to run natively on the host os.

Run on host:

Check which models are available: 
```
ollama ls
```

Download `qwen3-vl` (the most powerful vision-language model) when not available:
```
ollama pull qwen3-vl:4b-instruct
```

## Setting the environment variables

First, pay `.env.dev` a visit, you'll need to:
1. Change `IMAGE_DESCRIPTION_PROVIDER` and `OLLAMA_URL`

Create or adjust `.env.dev.local`. Make sure `IMAGE_DESCRIPTION_PROVIDER` is set with `ollama`.
In case `.env.dev.local` does not exists, run:

```
echo IMAGE_DESCRIPTION_PROVIDER=ollama >> .env.dev.local
echo OLLAMA_URL=http://localhost:11434 >> .env.dev.local
```
2. `OLLAMA_MODEL` may stay unchanged; it must match to an available model on the host.

## Build and run containers

Build and run containers:
```
docker compose -f compose.yaml -f compose.override.yaml -f compose.dev.yaml up -d --build
```


