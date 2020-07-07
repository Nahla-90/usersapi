
## Challenge Idea
We have two providers collect data from them in json files we need to read and make some filter operations on them to get the result

You can check the json files inside jsons folder 

- `DataProviderX` data is stored in [DataProviderX.json]
- `DataProviderY` data is stored in [DataProviderX.json]


`DataProviderX  schema is 
```
{
  parentAmount:200,
  Currency:'USD',
  parentEmail:'parent1@parent.eu',
  statusCode:1,
  registerationDate: '2018-11-30',
  parentIdentification: 'd3d29d70-1d25-11e3-8591-034165a3a613'
}
```

we have three status for `DataProviderX` 

- `authorised` which will have statusCode `1`
- `decline` which will have statusCode `2`
- `refunded` which will have statusCode `3`


`DataProviderY  schema is 
```
{
  balance:300,
  currency:'AED',
  email:'parent2@parent.eu',
  status:100,
  created_at: '22/12/2018',
  id: '4fc2-a8d1'
}
```

we have three status for `DataProviderY` 

- `authorised` which will have statusCode `100`
- `decline` which will have statusCode `200`
- `refunded` which will have statusCode `300`


## Acceptance Criteria

Implement this API `/api/v1/users`

- it should list all users which combine transactaions from all the available provider`DataProviderX` and `DataProviderY` )
- it should be able to filter resullt by payment providers for example `/api/v1/users?provider=DataProviderX` it should return users from DataProviderX
- it should be able to filter result three statusCode (`authorised`, `decline`, `refunded`) for example /api/v1/users?statusCode=authorised it should return all users from all providers that have status code authorised
- it should be able to filer by amount range for example `/api/v1/users?balanceMin=10&balanceMax=100` it should return result between 10 and 100 including 10 and 100
- it should be able to filer by `currency` 
- it should be able to combine all this filter together 

## The Evaluation

Task will be evaluated based on

1. Code quality
2. Application performance in reading large files 
3. Code scalability : ability to add  `DataProviderZ` by small changes
4. Unit tests coverage



# usersapi Installation
Users API (Symfony): http://usersapi.dev

First : Install virtual host on apache 

 1- create anew file usersapi.dev in your vhosts folder.
 2- add this code to it.
  <VirtualHost *:80>
      ServerName usersapi.dev
      ServerAlias www.usersapi.dev

      DocumentRoot /var/www/usersapi/public
      <Directory /var/www/usersapi/public>
          AllowOverride All
          Order Allow,Deny
          Allow from All
      </Directory>

      # uncomment the following lines if you install assets as symlinks
      # or run into problems when compiling LESS/Sass/CoffeeScript assets
      # <Directory /var/www/usersapi>
      #     Options FollowSymlinks
      # </Directory>

      ErrorLog /var/log/apache2/usersapi_error.log
      CustomLog /var/log/apache2/usersapi_access.log combined
  </VirtualHost>

3- add vhost name in hosts file in /etc/hosts
127.0.0.1   usersapi.dev

4- restart apache using #service apache2 restart
 
Second: Install Project ( You should have symfony installed)
1- get clone from project in /var/www
2- install required bundles 


Third: Just Try >> http://usersapi.dev/api/v1/users







