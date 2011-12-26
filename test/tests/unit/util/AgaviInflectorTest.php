<?php

class AgaviInflectorTest extends AgaviPhpUnitTestCase
{
	/**
	 * @dataProvider singularPluralTestData
	 */
	public function testSingularize($singular, $plural)
	{
		$this->assertEquals($singular, AgaviInflector::singularize($plural));
	}
	
	/**
	 * @dataProvider singularPluralTestData
	 */
	public function testPluralize($singular, $plural)
	{
		$this->assertEquals($plural, AgaviInflector::pluralize($singular));
	}
	
	public function singularPluralTestData()
	{
		return array(
			array("person"      , "people"),
			array("man"         , "men"),
			array("woman"       , "women"),
			array("child"       , "children"),
			array("search"      , "searches"),
			array("switch"      , "switches"),
			array("fix"         , "fixes"),
			array("box"         , "boxes"),
			array("sex"         , "sexes"),
			array("process"     , "processes"),
			array("address"     , "addresses"),
			array("case"        , "cases"),
			array("stack"       , "stacks"),
			array("wish"        , "wishes"),
			array("fish"        , "fish"),
			array("jeans"       , "jeans"),
			array("money"       , "money"),
			array("my money"    , "my money"),
			array("price"       , "prices"),
			array("rice"        , "rice"),
			array("category"    , "categories"),
			array("query"       , "queries"),
			array("ability"     , "abilities"),
			array("agency"      , "agencies"),
			array("movie"       , "movies"),
			array("archive"     , "archives"),
			array("move"        , "moves"),
			array("index"       , "indices"),
			array("wife"        , "wives"),
			array("safe"        , "saves"),
			array("half"        , "halves"),
			array("move"        , "moves"),
			array("salesperson" , "salespeople"),
			array("person"      , "people"),
			array("spokesman"   , "spokesmen"),
			array("man"         , "men"),
			array("woman"       , "women"),
			array("basis"       , "bases"),
			array("diagnosis"   , "diagnoses"),
			array("diagnosis_a" , "diagnosis_as"),
			array("datum"       , "data"),
			array("medium"      , "media"),
			array("stadium"     , "stadia"),
			array("analysis"    , "analyses"),
			array("node_child"  , "node_children"),
			array("child"       , "children"),
			array("experience"  , "experiences"),
			array("day"         , "days"),
			array("comment"     , "comments"),
			array("foobar"      , "foobars"),
			array("newsletter"  , "newsletters"),
			array("old_news"    , "old_news"),
			array("news"        , "news"),
			array("series"      , "series"),
			array("species"     , "species"),
			array("quiz"        , "quizzes"),
			array("perspective" , "perspectives"),
			array("ox"          , "oxen"),
			array("zebu ox"     , "zebu oxen"),
			array("photo"       , "photos"),
			array("buffalo"     , "buffaloes"),
			array("tomato"      , "tomatoes"),
			array("dwarf"       , "dwarves"),
			array("elf"         , "elves"),
			array("information" , "information"),
			array("equipment"   , "equipment"),
			array("bus"         , "buses"),
			array("status"      , "statuses"),
			array("status_code" , "status_codes"),
			array("mouse"       , "mice"),
			array("louse"       , "lice"),
			array("house"       , "houses"),
			array("octopus"     , "octopi"),
			array("virus"       , "viri"),
			array("alias"       , "aliases"),
			array("portfolio"   , "portfolios"),
			array("vertex"      , "vertices"),
			array("matrix"      , "matrices"),
			array("matrix_fu"   , "matrix_fus"),
			array("axis"        , "axes"),
			array("testis"      , "testes"),
			array("crisis"      , "crises"),
			array("white-rice"  , "white-rice"),
			array("white_rice"  , "white_rice"),
			array("rice"        , "rice"),
			array("shoe"        , "shoes"),
			array("horse"       , "horses"),
			array("prize"       , "prizes"),
			array("edge"        , "edges"),
			array("database"    , "databases"),
			array("cookie"      , "cookies"),
			array("cache"       , "caches"),
			array("|ice"        , "|ices"),
			array("|ouse"       , "|ouses"),
			array("foot"        , "feet"),
			array("cold foot"   , "cold feet"),
			array("cold_foot"   , "cold_feet"),
			array("bigfoot"     , "bigfoots"),
			array("tooth"       , "teeth"),
			array("dog_tooth"   , "dog_teeth"),
			array("sabertooth"  , "sabertooths"),
			array("goose"       , "geese"),
			array("mongoose"    , "mongooses"),
			array("criterion"   , "criteria"),
			array("cherry"      , "cherries"),
			array("lady"        , "ladies"),
			array("penny"       , "pennies"),
		);
	}
}

?>