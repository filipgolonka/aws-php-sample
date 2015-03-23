# ElasticBeanstalk application version remover

ElasticBeanstalk has a limit for stored application versions - you can have maximum 500 of it. 
This script can remove old application versions, base on configuration.

Basic configuration is in ```config.php.dist``` file. After ```composer install``` this file will be copied 
into ```config.php``` - you can edit it.

In ```client``` section you have to pass your AWS credentials.

Script will leave last ```leaveLastNVersions``` versions for applications matched into ```labelPatterns```

Script is based on [AWS SDK for PHP Sample project](https://github.com/awslabs/aws-php-sample)
