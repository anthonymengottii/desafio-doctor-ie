# Build do app Flutter Web e serve via nginx.
FROM ghcr.io/cirruslabs/flutter:3.41.9 AS build

WORKDIR /app
COPY pubspec.yaml pubspec.lock ./
RUN flutter pub get

COPY . .

# URL da API embutida no build (ajustavel via build-arg).
ARG API_BASE_URL=http://localhost:8000/api
RUN flutter build web --release --dart-define=API_BASE_URL=$API_BASE_URL

FROM nginx:alpine
COPY --from=build /app/build/web /usr/share/nginx/html
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
