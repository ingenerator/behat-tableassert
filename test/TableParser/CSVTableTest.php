<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace test\Ingenerator\BehatTableAssert\TableParser;

use Ingenerator\BehatTableAssert\TableParser\CSVTable;

class CSVTableTest extends \PHPUnit_Framework_TestCase
{

    public function provider_all_csv_parsers()
    {
        $reflection    = $this->getCsvTableReflection();
        $csv_table_dir = dirname($reflection->getFileName());
        $namespace     = $reflection->getNamespaceName();
        $parsers       = [];
        foreach (glob($csv_table_dir.'/CSV/*TableParser.php') as $class_file) {
            $class     = basename($class_file, '.php');
            $parsers[] = [$namespace.'\CSV\\'.$class];
        }

        return $parsers;
    }

    public function test_it_cannot_be_directly_constructed()
    {
        $reflection = $this->getCsvTableReflection();
        $this->assertTrue(
            $reflection->getMethod('__construct')->isProtected(),
            $reflection->getName().'::__construct should be protected'
        );
        $this->assertTrue(
            $reflection->getMethod('newInstance')->isProtected(),
            $reflection->getName().'::newInstance should be protected'
        );
    }

    /**
     * @dataProvider provider_all_csv_parsers
     */
    public function test_it_can_factory_all_known_csv_parsers($parser_class)
    {
        $factory = $this->newSubject();

        $class  = basename(str_replace('\\', '/', $parser_class));
        $method = 'make'.$class;
        $this->assertTrue(
            method_exists($factory, $method),
            "$method should be defined on $parser_class"
        );
        $this->assertInstanceOf($parser_class, $factory->$method());
    }

    /**
     * @return \ReflectionClass
     */
    protected function getCsvTableReflection()
    {
        $reflection = new \ReflectionClass('Ingenerator\BehatTableAssert\TableParser\CSVTable');

        return $reflection;
    }

    /**
     * @return CSVTable
     */
    protected function newSubject()
    {
        $reflection  = $this->getCsvTableReflection();
        $constructor = $reflection->getMethod('newInstance');
        $constructor->setAccessible(TRUE);
        $factory = $constructor->invoke(NULL);

        return $factory;
    }

}
