# Cronjobs

The following two jobs should run:

- queue:work --stop-when-empty --tries=5

  **NOTE**: only if you are not using the listen feature!
- crepes:send-reminders

An example configuration in crontab is:

```
0 10 * * * cd /.../inschrijvingen_core && php artisan subreminders:send >> /dev/null 2>&1
*/20 * * * * cd /.../inschrijvingen_core && php artisan queue:work --stop-when-empty --tries=5 >> /dev/null 2>&1
```
