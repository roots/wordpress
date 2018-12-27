FROM php:7

WORKDIR /app

RUN apt-get update \
    && apt-get install -y git \
    && rm -rf /var/lib/apt/lists/*

COPY bin bin/

CMD ["php", "./bin/update-repo.php"]
