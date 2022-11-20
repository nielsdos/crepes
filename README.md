<div align="center">
<h1>Crepes</h1>
<strong>A course and event registration web application</strong>
<br>
<em><ins>c</ins>ourses <ins>re</ins>gistration <ins>p</ins>latform and <ins>e</ins>nrollment <ins>s</ins>ervice</em>
<br><br>

[![codecov](https://img.shields.io/codecov/c/github/nielsdos/crepes?logo=codecov&style=for-the-badge&token=708N5Z15C5)](https://codecov.io/github/nielsdos/crepes)
![GitHub Workflow Status (branch)](https://img.shields.io/github/workflow/status/nielsdos/crepes/CI/main?style=for-the-badge)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=for-the-badge&logo=laravel)](https://laravel.com/docs/9.x/octane#introduction)
![PHP](https://img.shields.io/badge/PHP%3E=8.1-777BB4?style=for-the-badge&logo=php&logoColor=white)
![GitHub](https://img.shields.io/github/license/nielsdos/crepes?style=for-the-badge)

</div>

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#star-about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#toolbox-getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation-dependencies">Installation: Dependencies</a></li>
        <li><a href="#installation-set-up">Installation: Set-up</a></li>
        <li><a href="#running-the-development-server">Running the development server</a></li>
        <li><a href="#cronjobs">Cronjobs</a></li>
      </ul>
    </li>
    <li><a href="#compass-roadmap">Roadmap</a></li>
    <li><a href="#wave-contributing">Contributing</a></li>
    <li><a href="#scroll-license">License</a></li>
    <li><a href="#gem-acknowledgments">Acknowledgments</a></li>
  </ol>
</details>

## :star: About The Project

<table>
    <tr>
        <td><img src="https://user-images.githubusercontent.com/7771979/202927645-16e68c61-dbf9-49ae-af9f-2e1481db8ab6.png"></td>
        <td><img src="https://user-images.githubusercontent.com/7771979/202927721-ccba6ab0-af21-4844-96c1-a1b5fa125a27.png"></td>
        <td><img src="https://user-images.githubusercontent.com/7771979/202927735-536d7b47-db5b-4ce9-9b6b-032ec89ab1ef.png"></td>
    </tr>
</table>
<div align="center"><strong>Click to enlarge screenshots</strong></div>
<br>
<p>
This program allows users to create events in which others can register themselves in a user-friendly way.
It was originally created to organise courses, but it is more broadly applicable than that.
</p>

**Features**:
- Create events (or courses) split into different sessions, and in different groups
- Intuitive user-interface for both administrators and regular users
- Email reminders and notifications
- CSV and Excel exports of data
- ...

## :toolbox: Getting Started

Start by cloning the repository to a local directory.

### Prerequisites

* PHP >= 8.1, with XML and GD extensions enabled
* A Laravel-compatible database server (e.g. MySQL, MariaDB, PostgreSQL, ...)
* Yarn

### Installation: Dependencies

Start by installing the dependencies for the back-end and front-end:

```
$ cp .env.example .env
$ composer install --no-dev
$ yarn install
```

Build the front-end files:

```
$ yarn prod # or yarn dev (or watch) for development
```

You are now ready set-up the application.

### Installation: Set-up

You shall modify the `.env` file to configure the application. This includes settings for the database, reCAPTCHA settings, and email settings.
In particular, the following keys should be at least set-up:

- `MAIL_*`
- `DB_*`
- `NOCAPTCHA_*`, for reCAPTCHA

  To get reCAPTCHA keys, please use the [reCAPTCHA admin console](https://www.google.com/recaptcha/admin/create) and select reCAPTCHA v2.

You can furthermore also specify your desired time zone, locale, etc.

Finally, run the following command to set-up the application key:

```
$ php artisan key:generate
```

You can now set-up the database using the following command:

```
$ php artisan migrate
```

New users, such as the initial admin user, can be created using the following command and following the steps:

```
$ php artisan crepes:create-user
```

### Running the development server

The development server can now be started like with any Laravel project:

```
$ php artisan serve
```

### Cronjobs

The application uses a queue in the background to send reminders and notifications.
<br>
You can run the queue using `php artisan queue:listen` or by using a cronjob for `php artisan queue:work --stop-when-empty`.
Furthermore, the application uses a cronjob to send reminders using the command `php artisan crepes:send-reminders`.

An example cron configuration can be found in [crons.md](crons.md).

## :compass: Roadmap

- More generalisation from courses to events
- Allow end users to have more customization
- Allow news posts and other CMS-like features that support the content
- Improve UX

## :wave: Contributing

Contributions are always welcome. Check out the contribution guidelines in [CONTRIBUTING.md](CONTRIBUTING.md).

## :scroll: License

This project is distributed under the AGPL-3.0 license. See [LICENSE](LICENSE) for more information.

## :gem: Acknowledgments

 - [awesome-readme-template](https://github.com/Louis3797/awesome-readme-template/blob/main/README.md)
 - [Laravel](https://github.com/laravel/framework)
