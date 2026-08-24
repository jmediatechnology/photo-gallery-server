# AI

Everyone heard the old saying: 

> A picture is worth a thousand words

Let's figure out if Anthropic AI is able to describe those thousands words. 

Photo Gallery Server is able to send his photographs to Claude to let it generate a textual description.

## Generating descriptions by Photographs

Photo Gallery Server exposes the `/photographs/{id}/generate-description` route.
See `config/routes.yaml` at `photograph_generate_description`. 

Photo Gallery Server can only send its own Photographs to Claude. 
Claude in turn sends Photo Gallery a textual description based on the supplied Photograph, 
and Photo Gallery Server sends that textual description to the Front-end; it does not alter anything yet. 

The Front-end must examine the generated description and send a new `PATCH` request to `/photographs/{id}` 
to store the generated text.
