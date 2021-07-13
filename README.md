<p align="center">
  <img width="320" src="https://cp5.sgp1.cdn.digitaloceanspaces.com/zoro/laravue-cdn/laravue-logo-line.png">
</p>
<p align="center">
  <a href="https://lubicode.com">
    <img width="320" src="https://i.imgur.com/Sch5c05.png">
  </a>
</p>
<p align="center">
  <a href="https://laravel.com">
    <img src="https://img.shields.io/badge/laravel-7.3-brightgreen.svg" alt="vue">
  </a>
  <a href="https://github.com/vuejs/vue">
    <img src="https://img.shields.io/badge/vue-2.6.10-brightgreen.svg" alt="vue">
  </a>
  <a href="https://github.com/ElemeFE/element">
    <img src="https://img.shields.io/badge/element--ui-2.13.0-brightgreen.svg" alt="element-ui">
  </a>
  <a href="https://github.com/tuandm/laravue/blob/master/LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg" alt="license">
  </a>
</p>

# Pim4Supplier

* This is a PIM (a Product Information Management system). However, this system is directly connected to their webshop.

* A user can log in here and add new products. This can be done manually or by means of an (automatic) import using an API or CSV/XML.

Documentation: [https://doc.laravue.dev](https://doc.laravue.dev)
## Getting started

### Prerequisites

### Installing
```bash
# Clone the project and run composer
#step 1
composer install
#step 2
npm install
#step 3
# change env.txt to .env 
# change database info in .env
DB_DATABASE=databasename
DB_USERNAME=password
DB_PASSWORD=password
#step 4
php artisan key:generate
#step 5
php artisan migrate --seed
#step 6
npm run dev
#step 7
php artisan serve
```
#### running in development mode
```bash
npm run dev
php artisan serve
```
#### running in production mode
```bash
npm run production
php artisan serve
```

#### Docker
```sh
docker-compose up -d
```
Build static files within Laravel container with npm
```sh
# Get laravel docker container ID from containers list
docker ps

docker exec -it <container ID> npm run dev # or npm run watch
# Where <container ID> is the "laravel" container name, ex: src_laravel_1
```
Open http://localhost:8000 (laravel container port declared in `docker-compose.yml`) to access Laravue

## Running the tests
* Tests system is under development

## Deployment and/or CI/CD
This project uses [Envoy](https://laravel.com/docs/5.8/envoy) for deployment, and [GitLab CI/CD](https://about.gitlab.com/product/continuous-integration/). Please check `Envoy.blade.php` and `.gitlab-ci.yml` for more detail.

## Built with
* [Laravel](https://laravel.com/) - The PHP Framework For Web Artisans
* [Laravel Sanctum](https://github.com/laravel/sanctum/) - Laravel Sanctum provides a featherweight authentication system for SPAs and simple APIs.
* [spatie/laravel-permission](https://github.com/spatie/laravel-permission) - Associate users with permissions and roles.
* [VueJS](https://vuejs.org/) - The Progressive JavaScript Framework
* [Element](https://element.eleme.io/) - A  Vue 2.0 based component library for developers, designers and product suppliers
* [Vue Admin Template](https://github.com/PanJiaChen/vue-admin-template) - A minimal vue admin template with Element UI

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, please look at the [release tags](https://github.com/tuandm/laravue/tags) on this repository.

## Authors

* **Tuan Duong** - *Initial work* - [tuandm](https://github.com/tuandm).
* **Tony Tin Nguyen** - *Frontend and Designer* - [nguyenquangtin](https://github.com/nguyenquangtin).

See also the list of [contributors](https://github.com/tuandm/laravue/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE) file for details.

## Related projects

* [Laravue-core](https://github.com/tuandm/laravue-core) - Laravel package which provides core functionalities of Laravue.

## Acknowledgements

* [vue-element-admin](https://panjiachen.github.io/vue-element-admin/#/) A magical vue admin which insprited Laravue project.
* [tui.editor](https://github.com/nhnent/tui.editor) - Markdown WYSIWYG Editor.
* [Echarts](http://echarts.apache.org/) - A powerful, interactive charting and visualization library for browser.

