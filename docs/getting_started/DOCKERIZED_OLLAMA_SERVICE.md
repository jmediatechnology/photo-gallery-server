# Getting Started on dev with dockerized ollama service

Generate real image descriptions by running LLM's locally through Ollama.

## Hardware support

Ollama is supported on some Nvidia Graphics card.
See list of supported graphic cards: https://docs.ollama.com/gpu

If your graphic card is listed you can continue at the next section.

Requirements:
- Reserve at least ~6 GB of free RAM for the default model.
- Without a GPU, a description takes roughly 10 seconds to over a minute per photo. It runs asynchronously via Messenger, so the app stays responsive.
- If Ollama is selected but unreachable, description jobs fail and end up in the failed Messenger transport. There is no silent fallback to the fake generator.
- **macOS:** Docker cannot use the Apple GPU, continue with [host native ollama service](docs/getting_started/HOST_NATIVE_OLLAMA_SERVICE.md).

## Setting the environment variables

First, pay `.env.dev` a visit, you'll need to:
1. Change `IMAGE_DESCRIPTION_PROVIDER`

Create or adjust `.env.dev.local`. Make sure `IMAGE_DESCRIPTION_PROVIDER` is set with `ollama`. 
In case `.env.dev.local` does not exists, run:

```
echo IMAGE_DESCRIPTION_PROVIDER=ollama >> .env.dev.local
```

2. `OLLAMA_URL` and `OLLAMA_MODEL` may stay unchanged. 


Build and run containers:
```
docker compose -f compose.yaml -f compose.override.yaml -f compose.dev.yaml -f compose.ollama.yaml up -d --build
```

List models:
```
docker compose exec ollama ollama ls
```
