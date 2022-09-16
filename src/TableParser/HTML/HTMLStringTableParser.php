<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser\HTML;


use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;
use LibXMLError;
use Masterminds\HTML5;

/**
 * Parses an HTML string for a <table> element into a TableNode. The table must have a single row
 * in the <thead> and a <tbody> element (though this does not have to contain any rows). You can
 * skip additional rows in the table by marking up like <tr data-behat-table="ignore">.
 *
 * If you need to prefix cell content with some string for behat (for example to differentiate
 * two columns that would be visually different to a human user) add a data-behat-table-prefix
 * attribute.
 *
 *   <table>
 *     <thead>
 *       <tr data-behat-table="ignore">
 *         <th></th>
 *         <th colspan=2>Timings</th>
 *         <th></th>
 *       </tr>
 *       <tr>
 *         <th>Category</th>
 *         <th data-behat-table-prefix="Timings -">Before</th>
 *         <th data-behat-table-prefix="Timings -">After</th>
 *         <th>Count</th>
 *       </tr>
 *     </thead>
 *     <tbody>
 *       <tr>
 *         <th>Thing</th>
 *         <td>12.3</td>
 *         <td>14.2</td>
 *         <td>12</td>
 *       </tr>
 *       <tr>
 *         <th colspan="3">Total</th>
 *         <td>12</td>
 *       </tr>
 *     </tbody>
 *   </table>
 *
 * becomes:
 *
 *   | Category | Timings - Before | Timings - After | Count |
 *   | Thing    | 12.3             | 14.2            | 12    |
 *   | Total    | ...              | ...             | 12    |
 *
 * @package Ingenerator\BehatTableAssert\TableParser\HTML
 */
class HTMLStringTableParser
{

    /**
     * @param string $html
     *
     * @return \Ingenerator\BehatTableAssert\TableNode\PaddedTableNode
     */
    public function parse($html)
    {
        if ( ! (\is_string($html) && $html)) {
            throw new \InvalidArgumentException('Expected an HTML string');
        }

        $html_table = $this->parseHTMLString($html);

        if ($html_table->getName() !== 'table') {
            throw new \InvalidArgumentException(
                'Expected a <table> but got '.$html_table->getName()
            );
        }

        $rows = $this->parseTable($html_table);

        return new PaddedTableNode($rows);
    }

    /**
     * @param string $html
     *
     * @return \SimpleXMLElement
     */
    protected function parseHTMLString($html)
    {
        $old_use_internal_errors = \libxml_use_internal_errors(TRUE);
        try {
            $html5 = new HTML5();
            $dom = $html5->loadHTML(
              '<!DOCTYPE html><html>'
              .'<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>'
              .'<body>'.\trim($html).'</body>'
              .'</html>'
            );

            $table_elem = $dom->getElementsByTagName('body')->item(0)->firstChild;
            $table      = \simplexml_import_dom($table_elem);
            if ($errors = \libxml_get_errors()) {
                $this->throwInvalidHTMLException($html, $errors);
            }
        } finally {
            \libxml_clear_errors();
            \libxml_use_internal_errors($old_use_internal_errors);

        }

        return $table;
    }

    /**
     * @param string        $html
     * @param LibXMLError[] $errors
     */
    protected function throwInvalidHTMLException($html, $errors)
    {
        $msg = 'Invalid HTML string:';
        foreach ($errors as $error) {
            $msg .= \strtr(
                "\n #code:message (@line:column)",
                \array_map('trim', (array) $error)
            );
        }
        $msg .= "\n\n===HTML===\n$html";
        throw new \InvalidArgumentException($msg);
    }

    /**
     * @param \SimpleXMLElement $html_table
     *
     * @return array
     */
    protected function parseTable(\SimpleXMLElement $html_table)
    {
        $header = $this->parseRows($this->requireSingleChild($html_table, 'thead'));

        if (empty($header)) {
            throw new \InvalidArgumentException('No <tr> found in <thead>');
        } elseif (\count($header) > 1) {
            throw new \InvalidArgumentException(
                'Multiple <tr> found in <thead> - you can mark additional rows with data-behat-table="ignore"'
            );
        }

        $body = $this->parseRows($this->requireSingleChild($html_table, 'tbody'));

        return \array_merge($header, $body);
    }

    /**
     * @param \SimpleXMLElement $html_table
     * @param string            $tag_name
     *
     * @return \SimpleXMLElement
     */
    protected function requireSingleChild(\SimpleXMLElement $html_table, $tag_name)
    {
        if ( ! $child = $html_table->$tag_name) {
            throw new \InvalidArgumentException(
                'No <'.$tag_name.'> found in <'.$html_table->getName().'>'
            );
        }

        /** @var \SimpleXmlElement $child */

        if ($child->count() > 1) {
            throw new \InvalidArgumentException(
                'Multiple <'.$tag_name.'> found in <'.$html_table->getName().'>'
            );
        }

        return $child;
    }

    /**
     * @param \SimpleXMLElement $section
     *
     * @return array
     */
    protected function parseRows(\SimpleXMLElement $section)
    {
        $rows = [];
        foreach ($section->tr as $row) {
            if ( (string) $row['data-behat-table'] === 'ignore') {
                continue;
            }

            $rows[] = $this->findCellTextValues($row);
        }
        return $rows;
    }

    /**
     * @param \SimpleXMLElement $table_row
     *
     * @return string[]
     */
    protected function findCellTextValues(\SimpleXMLElement $table_row)
    {
        $row = [];
        foreach ($table_row->children() as $child) {
            /** @var \SimpleXMLElement $child */
            $row[] = $this->parseCellText($child);

            $colspan = (int) $child['colspan'];
            for ($i = 1; $i < $colspan; $i++) {
                $row[] = '...';
            }
        }

        return $row;
    }

    /**
     * @param \SimpleXmlElement $cell
     *
     * @return string
     */
    protected function parseCellText(\SimpleXmlElement $cell)
    {
        $text = \trim(\preg_replace('/\s+/', ' ', \dom_import_simplexml($cell)->textContent));

        if ($prefix = (string) $cell['data-behat-table-prefix']) {
            $text = $prefix.' '.$text;
        }

        return $text;
    }
}
