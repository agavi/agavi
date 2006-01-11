<?php


class MyTestView extends FormPHPView
{ 
	public function execute() {}
}


Class FormPHPViewTest extends UnitTestCase
{
	private $pfv;

	public function setUp()
	{
		$this->pfv = new MyTestView();
	}

	public function testFormTextReturnsAnInputTag()
	{
		$s = '<input name="bleh" id="bleh" type="text">';
		$this->assertIdentical($this->pfv->formText('bleh'), $s);
	}

	public function testFormTextReturnsInputTagWithAttributes()
	{
		$base = '<label>Bleh Label<input name="bleh" id="bleh" type="text"';

		$s = $base . ' size="10"></label>';
		$parm = array('size' => '10');
		$this->assertIdentical($this->pfv->formText('bleh', 'Bleh Label', $parm), $s);
		
		$s = $base .' size="10" value="mom"></label>';
		$parm += array('value' => 'mom');
		$this->assertIdentical($this->pfv->formText('bleh', 'Bleh Label', $parm), $s);
		
		$s = $base .' size="10" value="mom" maxlength="10"></label>';
		$parm += array('maxlength' => '10');
		$this->assertIdentical($this->pfv->formText('bleh', 'Bleh Label', $parm), $s);
	}

	public function testFormTextAreaReturnsTextAreaTags()
	{
		$s = '<textarea name="bleh" id="bleh"></textarea>';
		$this->assertIdentical($this->pfv->formTextArea('bleh'), $s);
	}

	public function testFormTextAreaSetsAndEscapesValue()
	{
		$s = '<label>Bleh Label' .
			  '<textarea name="bleh" id="bleh">Bob is &quot;cool&quot;</textarea></label>';
		$this->assertIdentical($this->pfv->formTextArea('bleh', 'Bleh Label', array('value'=>'Bob is "cool"')), $s);
	}

	public function testFormSelectReturnsSelectTags()
	{
		$s = '<select name="bleh" id="bleh"></select>';
		$this->assertIdentical($this->pfv->formSelect('bleh'), $s);
	}

	public function testFormSelectCanIncludeOptions()
	{
		$s = '<label>Bleh Label' .
			  '<select name="bleh" id="bleh"><option value="1">One</option><option value="2">Two</option></select></label>';
		$options[] = array('1','One');
		$options[] = array('2','Two');
		$parms = array('options' => $options);
		$this->assertIdentical($this->pfv->formSelect('bleh', 'Bleh Label', $parms), $s);
	}

	public function testFormSelectCanMakeMultipleSelect()
	{
		$options[] = array('1','Apple');
		$options[] = array('2','Orange');
		$options[] = array('3','Bob');
		$parms = array('multiple' => true, 'options' => $options);
		$tag = $this->pfv->formSelect('bleh', 'Bleh Label', $parms);
		$this->assertWantedPattern('/select name="bleh\[\]" multiple id="bleh"/', $tag); 
	}
	
	public function testFormSelectCanIncludeSelectedOption()
	{
		$options[] = array('1','Apple');
		$options[] = array('2','Orange',true); // if there's a value in the 3rd element, the option's selected
		$options[] = array('3','Bob');
		$parms = array('options' => $options);
		$tag = $this->pfv->formSelect('bleh', 'Bleh Label', $parms);
		$this->assertWantedPattern('/option value="2" selected/', $tag); 
		$this->assertNoUnwantedPattern('/option value="1" selected/', $tag); 
	}

	public function testFormSelectCanIncludeMultipleSelectedOptions()
	{
		$options[] = array('1','Apple');
		$options[] = array('2','Orange',true); // if there's a value in the 3rd element, the option's selected
		$options[] = array('3','Bob', 'stud');
		$parms = array('multiple' => true, 'options' => $options);
		$tag = $this->pfv->formSelect('bleh', 'Bleh Label', $parms);
		$this->assertWantedPattern('/option value="2" selected/', $tag); 
		$this->assertWantedPattern('/option value="3" selected/', $tag); 
		$this->assertNoUnwantedPattern('/option value="1" selected/', $tag); 
	}

	public function testFormWrappersReturnCannedTypeTags()
	{
		$this->assertWantedPattern('/ type="submit"/', $this->pfv->formSubmit('test'));
		$this->assertWantedPattern('/ type="hidden"/', $this->pfv->formHidden('test'));
		$this->assertWantedPattern('/ type="button"/', $this->pfv->formButton('test'));
		$this->assertWantedPattern('/ type="radio"/', $this->pfv->formRadio('test'));
		$this->assertWantedPattern('/ type="checkbox"/', $this->pfv->formCheckbox('test'));
		$this->assertWantedPattern('/ type="password"/', $this->pfv->formPassword('test'));
		$this->assertWantedPattern('/ type="image"/', $this->pfv->formImage('test'));
	}

	public function testFormStartFieldset()
	{
		$this->assertEqual($this->pfv->formStartFieldset(), '<fieldset>');
		$this->assertEqual($this->pfv->formStartFieldset('Fieldset Legend'), '<fieldset><legend>Fieldset Legend</legend>');
	}

	public function testFormEndFieldset()
	{
		$this->assertEqual($this->pfv->formEndFieldset(), '<div class="clearer"></div></fieldset>');
	}

}

?>