<?php

declare(strict_types=1);

namespace Drupal\training_correction\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Http\ClientFactory;

/**
 * Correction for TP Service.
 */
class TpService {

  /**
   * The Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The serializer.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ClientFactory $client_factory,
    Json $serializer
  ) {
    $this->client = $client_factory->fromOptions([
      'base_uri' => 'https://pokeapi.co/api/v2/',
    ]);
    $this->serializer = $serializer;
  }

  /**
   * Return a list of pokemon returned by the api.
   *
   * @param int $start
   *   The pokedex number that starts the list.
   * @param int $limit
   *   The number of pokemon to return.
   *
   * @return array
   *   An array of pokemons.
   */
  public function getPokemons(int $start, int $limit = 20): array {
    $response = $this->client
      ->request('GET', 'pokemon', [
        'query' => [
          'offset' => $start,
          'limit' => $limit,
        ],
      ]);

    if ($response->getStatusCode() != 200) {
      return [];
    }

    // Get the contents.
    // https://docs.guzzlephp.org/en/latest/quickstart.html#using-responses
    $contents = $response->getBody()->getContents();

    // Use the serailization service to decode the content.
    $pokemons = $this->serializer->decode($contents);

    // Return items list.
    return $pokemons['results'] ?? [];
  }

}
