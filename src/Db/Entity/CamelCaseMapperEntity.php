<?php
namespace Clearbooks\Labs\Db\Entity;

abstract class CamelCaseMapperEntity
{
    const WORD_DELIMITER = "_";

    /**
     * @param array $data
     */
    public function __construct( array $data )
    {
        foreach ( $data as $key => $value ) {
            $property = $this->convertToCamelCase( $key );

            if ( property_exists( $this, $property ) ) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [ ];
        $transientProperties = $this->getTransientProperties();

        foreach ( $this as $key => $value ) {
            if ( in_array( $key, $transientProperties ) ) {
                continue;
            }

            $data[$this->convertToDbKey( $key )] = $value;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getTransientProperties()
    {
        return [ ];
    }

    /**
     * @param string $word
     * @return string
     */
    private function convertToCamelCase( $word )
    {
        return lcfirst( preg_replace_callback(
                                '/' . self::WORD_DELIMITER . '([a-z0-9])/i',
                                function ( $matches ) {
                                    return strtoupper( $matches[1] );
                                },
                                $word
                        ) );
    }

    /**
     * @param string $word
     * @return string
     */
    private function convertToDbKey( $word )
    {
        return strtolower( preg_replace( '/(.)([A-Z0-9])/', '$1' . self::WORD_DELIMITER . '$2', $word ) );
    }
}
