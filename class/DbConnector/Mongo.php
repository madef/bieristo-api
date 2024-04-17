<?php

namespace Bieristo\DbConnector;

class Mongo
{
    const WRITE_CONCERN_PROCUPATION = \MongoDB\Driver\WriteConcern::MAJORITY;
    const WRITE_CONCERN_TIMEOUT = 1000;

    protected static $instance;
    protected $db;
    protected $manager;
    protected $client;

    protected function __construct()
    {
        $this->db = MONGO_DBNAME;
        $this->manager = new \MongoDB\Driver\Manager('mongodb://'.MONGO_USER.':'.MONGO_PASSWORD.'@'.MONGO_HOST.':'.MONGO_PORT.'/'.MONGO_DBNAME);
        $this->client = new \MongoDB\Client('mongodb://'.MONGO_USER.':'.MONGO_PASSWORD.'@'.MONGO_HOST.':'.MONGO_PORT.'/'.MONGO_DBNAME);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Mongo();
        }

        return self::$instance;
    }

    public function checkConnection()
    {
        try {
            $this->client->listDatabases();
        } catch (\MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
            return false;
        } catch (\MongoDB\Driver\Exception\AuthenticationException $e) {
            return false;
        }

        return true;
    }

    public function add($collection, $document, $commit = true)
    {
        $this->getBulk($collection)->insert($document);

        if ($commit) {
            return $this->commit($collection)['inserted_count'];
        }
    }

    public function find($collection, $filters, $options = [])
    {
        if (empty($filters) && empty($options)) {
            try {
                throw new \Exception();
            } catch (\Exception $e) {
                echo "Empty collection\n";
                echo $e->getTraceAsString();
                die;
            }
        }

        return $this->client->{$this->db}->{$collection}->find($filters, $options);
    }

    public function count($collection, $filters)
    {
        return $this->client->{$this->db}->{$collection}->count($filters);
    }

    public function findOne($collection, $filters = [], $options = [])
    {
        return $this->client->{$this->db}->{$collection}->findOne($filters, $options);
    }

    public function update($collection, $filters, $document, $commit = true, $updateOptions = null)
    {
        $this->getBulk($collection)->update($filters, $document, $updateOptions);

        if ($commit) {
            return $this->commit($collection)['modified_count'];
        }
    }

    public function delete($collection, $filters = [], $commit = true)
    {
        $this->getBulk($collection)->delete($filters, array('limit'=>1));

        if ($commit) {
            return $this->commit($collection)['deleted_count'];
        }
    }

    public function commit($collection)
    {
        $bulk = $this->getBulk($collection);
        if (!count($bulk)) {
            return [
                'inserted_count' => 0,
                'modified_count' => 0,
                'deleted_count' => 0,
            ];
        }

        $writeConcern = new \MongoDB\Driver\WriteConcern(self::WRITE_CONCERN_PROCUPATION, self::WRITE_CONCERN_TIMEOUT);
        $result = $this->manager->executeBulkWrite($this->db.'.'.$collection, $bulk, $writeConcern);

        $this->clearBulk($collection);

        return [
            'inserted_count' => $result->getInsertedCount(),
            'modified_count' => $result->getModifiedCount(),
            'deleted_count' => $result->getDeletedCount(),
        ];
    }

    private function getBulk($collection)
    {
        if (!isset($this->bulkCollection[$collection])) {
            $this->bulkCollection[$collection] = new \MongoDB\Driver\BulkWrite;
        }

        return $this->bulkCollection[$collection];
    }

    private function clearBulk($collection)
    {
        if (!isset($this->bulkCollection[$collection])) {
            return;
        }

        $this->bulkCollection[$collection] = new \MongoDB\Driver\BulkWrite;
    }

    public function deleteIndex($collection, $name)
    {
        $data = [
            'deleteIndexes' => $collection,
            'index' => $name,
        ];

        $command = new \MongoDB\Driver\Command($data);
        $this->manager->executeCommand($this->db, $command);
    }

    public function createIndex($collection, $name, $keys, $options = [])
    {
        try {
            $this->deleteIndex($collection, $name);
        } catch (\MongoDB\Driver\Exception\CommandException $e) {
            // Index do not exists
            // Do nothing
        }

        $data = [
            'createIndexes' => $collection,
            'indexes' => [
                [
                    'name' => $name,
                    'key'  => $keys,
                    'unique' => (isset($options['unique']) && $options['unique']) ? true : false,
                    'ns' => "{$this->db}.$collection",
                ]
            ],
        ];

        $command = new \MongoDB\Driver\Command($data);

        $this->manager->executeCommand($this->db, $command);
    }
}
