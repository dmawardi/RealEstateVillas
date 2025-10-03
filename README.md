# RealEstateVillas


## Server Setup

### Upload PHP Updates
You will need to change the settings of php in the php.ini file. This is to allow uploads of greater than 2MB to not fail.

```shell
# To check current settings
php -i | grep -E "(upload_max_filesize|post_max_size|max_file_uploads|memory_limit)"
```


After making the changes, you will need to restart apache and other server apps
```shell
# Find php.ini location
php --ini

# Edit the file (usually one of these)
sudo nano /etc/php/8.2/apache2/php.ini
sudo nano /etc/php/8.2/fpm/php.ini

# Restart web server
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```