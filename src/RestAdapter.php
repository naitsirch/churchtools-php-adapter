<?php

namespace Naitsirch\ChurchTools\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Adapter for the ChurchTools' REST API.
 *
 * @author naitsirch <naitsirch@e.mail.de>
 * @see https://demo.church.tools/api
 */
class RestAdapter
{
    protected HttpClientInterface $client;

    protected string $apiBaseUrl;

    protected string $token;

    protected string $userAgent;
    
    public function __construct(HttpClientInterface $client, string $apiBaseUrl, string $token)
    {
        $this->client = $client;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        $this->token = $token;

        $this->userAgent = 'naitsirch-churchtools-php-adapter/0.1 ' . php_uname('s');
    }

    /**
     * Get information about API version.
     */
    public function getInfo(): array
    {
        $content = $this->request('GET', '/info')->getContent();

        return json_decode($content, true);
    }

    /**
     * Currently logged in user.
     */
    public function getWhoami(): array
    {
        $content = $this->request('GET', '/whoami')->getContent();

        return json_decode($content, true);
    }

    /**
     * Get all persons.
     *
     * This endpoint gives you all the people you are allowed to see. Each
     * person object holds only those fields you may see. You will get at least
     * an empty array even if you cannot see any person. The results are sorted
     * by lastname, firstname.
     *
     * We distinguish between `date` and `date-time` fields. `date` is a ISO
     * representation like `YYYY-MM-DD`. On the other hand, for `date-time` we
     * return and accept a W3C Zulu date string. Example `1994-11-05T08:15:30Z`
     *
     * @param array $params Parameters to filter the result. The following keys
     *                      are available:
     *                      - ids              array[integer]  Array of person ids
     *                      - status_ids       array[integer]  Filter by status id
     *                      - campus_ids       array[integer]  Filter by campus id
     *                      - birthday_before  string($date)   Filter by birthdays before that date (Format: YYYY-MM-DD)
     *                      - birthday_after   string($date)   Filter by birthdays after that date (Format: YYYY-MM-DD)
     *                      - is_archived      boolean         Show only archived or not archived people
     *                      - page             integer         Page number to show page in pagenation. If empty, start at first page. Default value : 1
     *                      - limit            integer         Number of results per page. Default value : 10
     */
    public function getPersons(array $params = []): array
    {

        $response = $this->request('GET', '/persons', [
            'query' => $params,
        ]);
        $content = $response->getContent();

        return json_decode($content, true);
    }

    public function getPersonLoginToken(int $personId): string
    {
        $apiPath = sprintf('/persons/%d/logintoken', $personId);
        $response = $this->request('GET', $apiPath);
        $content = $response->getContent();

        return json_decode($content, true)['data'];
    }

    protected function request(string $method, string $apiPath, array $options = []): ResponseInterface
    {
        if ($method === 'GET' && !empty($options['body'])) {
            throw new \InvalidArgumentException('To send request params to the API the POST or PUT methods have to be used.');
        }

        if ($apiPath[0] !== '/') {
            throw new \InvalidArgumentException(sprintf('API path "%s" has to start with a forward slash.', $apiPath));
        }

        $options = array_replace_recursive([
            'headers' => [
                'Authorization' => 'Login ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ], $options);

        return $this->client->request($method, $this->apiBaseUrl . $apiPath, $options);
    }
}
