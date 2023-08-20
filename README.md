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

Also to do:
- Make the HTTP Client work with both caching and parallel requests
- Use GraphQL instead of REST
- Because the results are paginated in the frontend, there's actually no need to fetch all the characters from the 
  API at once. I would just need to fetch those shown in the current page.
- Use PHP-FPM instead of Apache in the Docker image
