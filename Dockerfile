FROM nginx:alpine

WORKDIR /app

COPY package.json .
RUN npm install

COPY . .

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
