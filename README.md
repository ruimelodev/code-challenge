## Description
Velv code challenge.

### Reviewers
Relevant code can be found here:

* [app/Http/Controllers/Api/Computers.php](https://github.com/ruimelodev/code-challenge/blob/master/app/Http/Controllers/Api/Computers.php)
* [tests/Feature/Api/ComputersTest.php](https://github.com/ruimelodev/code-challenge/blob/master/tests/Feature/Api/ComputersTest.php)


## Install
```
$ git clone git@github.com:ruimelodev/code-challenge.git

$ composer install

$ cp .env.example .env

$ php artisan key:generate
```

## Run
```
$ php artisan serve
```
Open: http://127.0.0.1:8000/api/computers


## Test
```
$ php artisan test
```

## Test with Postman
Import [collection](https://github.com/ruimelodev/code-challenge/blob/master/storage/postman/code_challenges.postman_collection.json) to your Postman App.

To test the hosted project, change url to: http://velv-code-challenge.herokuapp.com/api/computers