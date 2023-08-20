# Rick and Morty assignment

Symfony app for searching Rick and Morty characters, using the [Rick and Morty API](https://rickandmortyapi.com/).

Build the Docker image and run the container:
```bash
docker build -t rick-and-morty-app .
docker run -p 80:80 rick-and-morty-app
```
The app will be available at http://localhost.

## Notes
Mainly due to time constraints:
- The frontend is _very_ minimalistic and SSR only (no AJAX/SPA)
- The test suite is just drafted and currently includes only a few tests.
 
If I had more time to work on this project, these are the areas where I would focus on making additional improvements:
- validation and handle exception
- PHPStan CS Fixer
- other stuff michael
- altri improvements notion
