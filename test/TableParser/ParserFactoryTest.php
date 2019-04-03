<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser;


use Ingenerator\BehatTableAssert\TableParser\CSVTable;

abstract class ParserFactoryTest extends \PHPUnit\Framework\TestCase
{
    const SUBJECT_CLASS = '*define me*';
    const PARSER_GROUP = '*define me*';

    public function test_it_cannot_be_directly_constructed()
    {
        $reflection = $this->getSubjectReflection();
        $this->assertTrue(
            $reflection->getMethod('__construct')->isProtected(),
            $reflection->getName().'::__construct should be protected'
        );
        $this->assertTrue(
            $reflection->getMethod('newInstance')->isProtected(),
            $reflection->getName().'::newInstance should be protected'
        );
    }

    public function provider_all_parsers()
    {
        $reflection    = $this->getSubjectReflection();
        $csv_table_dir = \dirname($reflection->getFileName());
        $namespace     = $reflection->getNamespaceName();
        $parsers       = [];
        foreach (\glob($csv_table_dir.'/'.static::PARSER_GROUP.'/*TableParser.php') as $class_file) {
            $class     = \basename($class_file, '.php');
            $parsers[] = [$namespace.'\\'.static::PARSER_GROUP.'\\'.$class];
        }

        return $parsers;
    }

    /**
     * @dataProvider provider_all_parsers
     */
    public function test_it_can_factory_all_known_csv_parsers($parser_class)
    {
        $factory = $this->newSubject();

        $class  = \basename(\str_replace('\\', '/', $parser_class));
        $method = 'make'.$class;
        $this->assertTrue(
            \method_exists($factory, $method),
            "$method should be defined on $parser_class"
        );
        $this->assertInstanceOf($parser_class, $factory->$method());
    }

    /**
     * @return \ReflectionClass
     */
    protected function getSubjectReflection()
    {
        $reflection = new \ReflectionClass(static::SUBJECT_CLASS);

        return $reflection;
    }

    /**
     * @return CSVTable
     */
    protected function newSubject()
    {
        $reflection  = $this->getSubjectReflection();
        $constructor = $reflection->getMethod('newInstance');
        $constructor->setAccessible(TRUE);
        $factory = $constructor->invoke(NULL);

        return $factory;
    }
}
