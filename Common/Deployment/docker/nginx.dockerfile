FROM nginx:alpine

ARG SWAGGER_UI_NGINX_AUTH_USER
ARG SWAGGER_UI_NGINX_AUTH_PASSWD

RUN apk update; \
    apk add apache2-utils; \
    htpasswd -bcB -C 10 /etc/nginx/.htpasswd ${SWAGGER_UI_NGINX_AUTH_USER} ${SWAGGER_UI_NGINX_AUTH_PASSWD};