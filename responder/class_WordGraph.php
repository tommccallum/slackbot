<?php

// Build up a word graph of the relationships in the sentence
// The goal is to answer very simple questions and to identify
// unknowns we can ask the user about.
// A word graph is associated with either the World or a particular User or a single conversation.

class WordGraph
{
    private $world = [];

    public function __construct()
    {
    }

    // Tom PLAYS (with a) ball
    // Tom OWNS a dog
    // Tom OWNS two dogs - here two is the count
    // These are 2 items in the word we are setting up
    public function addRelationship($nounA, $verb, $nounB, $count=1)
    {
        $objectA = &$this->findOrInsertObject($nounA);
        $objectB = &$this->findOrInsertObject($nounB);
        $this->world['relationships'][] = [
            'nodeA' => $objectA,
            'nodeB' => $objectB,
            'rel' => $verb,
            'count' => $count
        ];
    }

    public function normaliseNoun($word)
    {
        return strtolower($word);
    }

    public function findOrInsertObject($noun)
    {
        foreach ($this->world['objects'] as &$object) {
            if ($object['normalised_name'] == $this->normaliseNoun($noun)) {
                return $object;
            }
        }
        return null;
    }

    // The ball is ROUND.
    // Tom IS a student.
    public function addFact($noun, $adjective)
    {
        $object = &$this->findOrInsertObject($noun);
        $object['properties'][] = [
            // TODO may be we strip these using the stemmer
            'name' => $adjective
        ];
    }

    // IS A
    // This is a generic relationship
    public function addHierarchyRelationship($nounA, $nounB)
    {
    }

    // A proper noun for instance a Person, Place
    public function addNamedObject($noun, $type)
    {
        $this->world['objects'][] = [
            'name' => $noun,
            'normalised_name' => $this->normaliseNoun($noun),
            'type' => $type
        ];
    }

    // ball
    public function addObject($noun)
    {
        $this->world['objects'][] = [
            'name' => $noun,
            'normalised_name' => $this->normaliseNoun($noun),
            'type' => "object"
        ];
    }

    public function saveWorld($file)
    {
        $contents = json_encode($this->world);
        file_put_contents($file, $contents);
    }

    /**
     * Add a new set of information to the world.
     *
     * @param [type] $file
     * @return void
     */
    public function include($file)
    {
        $contents = file_get_contents($file);

        if (!isset($this->world)) {
            $this->world = json_decode($contents, true);
        } else {
            throw new \Exception("merging worlds not available");
        }
    }
}
