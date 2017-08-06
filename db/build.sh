#!/bin/sh

set -e

docker run --rm -v $(pwd):/backup -v cms_data:/app -v cms_db:/data akilli/base tar cvf /backup/db.tar /app /data
