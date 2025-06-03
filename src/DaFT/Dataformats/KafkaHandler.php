<?php
namespace DaFT\Dataformats;

/**
 * Class KafkaHandler
 *
 * Executes queries against a Kafka REST Proxy via a cURL call.
 * - This class is designed to query a Kafka REST Proxy endpoint to retrieve metadata for a given topic.
 * - It supports configurable queries (with the "query" parameter used as the topic name) and handles JSON responses.
 * - It inherits common functionality from `GenericHandler`, such as data normalization and filtering.
 *
 * Security Notice:
 * - Ensure that the Kafka REST Proxy endpoint is secured to prevent unauthorised access with IP restrictions.
 * - This class does not handle authentication; if required, extend the functionality accordingly.
 *
 * Usage Example:
 * ```
 *     type          => kafka
 *     source        => http://localhost
 *     port          => 8082
 *     query         = my-topic
 *     group         = my-consumer-group
 * #   consumer_name = my-consumer
 *     etc...
 * ```
 *  consumer_name is optional
 *
 *  NOTE: THIS IS A WORK IN PROGRESS AND NOT YET FUNCTIONAL. THIS NEEDS TESTING AND VALIDATION.
 * 
 * 
 * ```
 *
 * Design Considerations:
 * - This class promotes flexible and efficient querying of Kafka topic metadata via the REST Proxy.
 * - It handles network and JSON decoding errors gracefully.
 *
 * @author Andy Dixon
 * @created 2025-04-08
 * @namespace DaFT\Dataformats
 */
class KafkaHandler extends GenericHandler
{
     /**
     * Handles consuming messages from a Kafka topic via the REST Proxy.
     *
     * @return array An array of messages or error details.
     */
    public function handle(): array
    {
        // Retrieve configuration details
        $host = rtrim($this->config['source'] ?? '', '/');
        $port = $this->config['port'] ?? 8082;
        $topic = $this->config['query'] ?? '';
        $group = $this->config['group'] ?? 'default_group';
        // Use a provided consumer name or generate one uniquely
        $consumerName = $this->config['consumer_name'] ?? 'consumer_' . uniqid();

        // Validate the Kafka host URL and topic
        if (empty($host) || !filter_var($host, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid Kafka host URL', 'topic' => $topic];
        }
        if (empty($topic)) {
            return ['error' => 'No topic specified in query'];
        }

        // --------------------------------------------------
        // Step 1: Create Consumer Instance
        // --------------------------------------------------
        $createUrl = "{$host}:{$port}/consumers/{$group}";
        $payload = json_encode([
            'name' => $consumerName,
            'format' => 'json',
            'auto.offset.reset' => 'earliest'
        ]);

        $ch = curl_init($createUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/vnd.kafka.v2+json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => "Error creating consumer: " . $error];
        }
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($statusCode !== 200 && $statusCode !== 409) { // Some setups return 409 if the consumer already exists
            curl_close($ch);
            return ['error' => "Failed to create consumer instance. HTTP Status: $statusCode"];
        }
        curl_close($ch);

        $consumerInfo = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => "Invalid JSON response when creating consumer",
                'json_error' => json_last_error_msg()
            ];
        }
        $baseUri = $consumerInfo['base_uri'] ?? null;
        if (!$baseUri) {
            return ['error' => 'Consumer base_uri not returned in response'];
        }

        // --------------------------------------------------
        // Step 2: Subscribe the Consumer to the Topic
        // --------------------------------------------------
        $subscriptionUrl = $baseUri . "/subscription";
        $payload = json_encode(['topics' => [$topic]]);
        $ch = curl_init($subscriptionUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/vnd.kafka.v2+json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => "Error subscribing to topic: " . $error];
        }
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($statusCode !== 204) { // Expecting 204 No Content on successful subscription
            return ['error' => "Failed to subscribe to topic. HTTP Status: $statusCode"];
        }

        // --------------------------------------------------
        // Step 3: Poll for Messages from the Topic
        // --------------------------------------------------
        $pollUrl = $baseUri . "/records";
        $ch = curl_init($pollUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => "Error polling messages: " . $error];
        }
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($statusCode !== 200) {
            return ['error' => "Failed to poll messages. HTTP Status: $statusCode"];
        }
        $messages = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Invalid JSON response when polling messages',
                'json_error' => json_last_error_msg()
            ];
        }

        // --------------------------------------------------
        // Step 4: Clean Up - Delete the Consumer Instance
        // --------------------------------------------------
        $deleteUrl = $baseUri;
        $ch = curl_init($deleteUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/vnd.kafka.v2+json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);

        // Normalize the message array (if required) and filter data as per GenericHandler's logic
        $messages = $this->normaliseArray($messages);
        return $this->filterData($messages);
    }
}
