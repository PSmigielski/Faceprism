# Faceprism

## Getting Started

Prerequisites for backend:

* Composer
* MariaDB server
* openssl

prerequisites for frontend:

* NodeJS
* npm

## Setup project

1. clone this repo

   ```text
   $ git clone git@github.com:PSmigielski/Faceprism.git
   ```

2. Install required dependencies for backend and frontend

   ```text
   $ npm install 
   $ composer install
   ```

     3. create directiory for public and private keys and generate them

```text
$ mkdir config/jwt 
$ openssl genrsa -out config/jwt/private.pem -aes256 4096 
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Used technologies and libraries

* Symfony 5
* Mercure
* MariaDB 
* Doctrine
* Twig
* React
* Formik
* Gsap
* React Context API

