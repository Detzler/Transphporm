<?php
use Transphporm\Builder;
class TransphpormTest extends PHPUnit_Framework_TestCase {

	public function testContentSimple() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		$css = 'ul li {content: data(user);}';


		$data = new \stdclass;
		$data->user = 'tom';

		
		$template = new Builder($template, $css, Builder::STRING);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output($data)); 
	}


	public function testContentObject() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		$css = 'ul li {content: data(user.name);}';


		$data = new stdclass;
		$data->user = new stdclass;
		$data->user->name = 'tom';

		
		$template = new \Transphporm\Builder($template, $css, Builder::STRING);
		
		$this->assertEquals('<ul><li>tom</li></ul>' ,$template->output($data)); 
	}


	public function testRepeatBasic() {
		$template = '
				<ul><li>TEST1</li></ul>
		';

		//When using repeat to repeat some data, set the content to the data for the iteration
		$css = 'ul li {repeat: data(list); content: iteration()}';


		$data = new stdclass;
		$data->list = ['One', 'Two', 'Three'];

		
		$template = new \Transphporm\Builder($template, $css, Builder::STRING);
		
		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output($data)); 
	}


	public function testRepeatObject() {
		$template = '
				<ul><li>TEST1</li></ul>
		';


		//This time read a specific value from the data of the current iteration
		$css = 'ul li {repeat: data(list); content: iteration(id)}';


		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->id = 'One';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->id = 'Two';
		$data->list[] = $two;

		$three = new stdclass;		
		$three->id = 'Three';
		$data->list[] = $three;
		
		$template = new \Transphporm\Builder($template, $css, Builder::STRING);
		
		$this->assertEquals('<ul><li>One</li><li>Two</li><li>Three</li></ul>' ,$template->output($data)); 
	}

	private function stripTabs($str) {
		return trim(str_replace("\t", '', $str));
	}

	public function testRepeatObjectChildNode() {
		$template = '
				<ul>
					<li>
						<span>TEST1</span>
					</li>
				</ul>
		';

		//Rather than setting the value to the 
		$css = 'ul li {repeat: data(list);}
		ul li span {content: iteration(id)}';


		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->id = 'One';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->id = 'Two';
		$data->list[] = $two;

		$three = new stdclass;		
		$three->id = 'Three';
		$data->list[] = $three;
		
		$template = new \Transphporm\Builder($template, $css, Builder::STRING);
		
		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<span>One</span>
			</li><li>
				<span>Two</span>
			</li><li>
				<span>Three</span>
			</li>
		</ul>') ,$this->stripTabs($template->output($data))); 
	}

	public function testRepeatObjectChildNodes() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }';


		$template = new \Transphporm\Builder($template, $css, Builder::STRING);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
				<span>One</span>
			</li><li>
				<h2>2</h2>
				<span>Two</span>
			</li><li>
				<h2>3</h2>
				<span>Three</span>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)));

	}


	public function testQuotedContent() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "TEST";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals('<h1>TEST</h1>', $template->output());
	}


	public function testQuotedContentWithEscape() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "TEST\"TEST";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals('<h1>TEST"TEST</h1>', $template->output());
	}

	public function testMultipleContentValues() {
		$template = '<h1>Heading</h1>';

		$tss = 'h1 {content: "A", "B";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals('<h1>AB</h1>', $template->output());
	}


	public function testMatchClassAndTag() {
		$template = '<h1>Test 1</h1><h1 class="test">Heading</h1><h1>Test 2</h1>';

		$tss = 'h1.test {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals('<h1>Test 1</h1><h1 class="test">REPLACED</h1><h1>Test 2</h1>', $template->output());
	}

	public function testMatchClassChild() {
		$template = '
		<div>
			<span class="foo">test</span>
			<span class="bar">test</span>
		</div>
		';

		$tss = 'div .foo {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);
		$this->assertEquals($this->stripTabs('<div>
			<span class="foo">REPLACED</span>
			<span class="bar">test</span>
		</div>'), $this->stripTabs($template->output()));
	}

	public function testChildNodeMatcher() {
		$template = '
		<div>
			<span class="foo">test</span>
			<span class="bar">test</span>
		</div>
		';

		$tss = 'div > .foo {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);
		$this->assertEquals($this->stripTabs('<div>
			<span class="foo">REPLACED</span>
			<span class="bar">test</span>
		</div>'), $this->stripTabs($template->output()));
	}


	public function testAttributeSelector() {
		$template = '
		<div>
			<textarea name="foo">foo</textarea>
			<textarea>bar</textarea>
		</div>
		';

		$tss = '[name="foo"] {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);
		$this->assertEquals($this->stripTabs('<div>
			<textarea name="foo">REPLACED</textarea>
			<textarea>bar</textarea>
		</div>'), $this->stripTabs($template->output()));
	}


	//check that it's not due to the order of the HTML
	public function testAttributeSelectorB() {
		$template = '
		<div>
			<textarea>bar</textarea>
			<textarea name="foo">foo</textarea>			
		</div>
		';

		$tss = '[name="foo"] {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);
		$this->assertEquals($this->stripTabs('<div>
			<textarea>bar</textarea>
			<textarea name="foo">REPLACED</textarea>
		</div>'), $this->stripTabs($template->output()));
	}


	public function testAttributeSelectorC() {
		$template = '
		<div>
			<a name="foo">a link</a>
			<textarea name="foo">foo</textarea>			
		</div>
		';

		$tss = 'textarea[name="foo"] {content: "REPLACED";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);
		$this->assertEquals($this->stripTabs('		<div>
			<a name="foo">a link</a>
			<textarea name="foo">REPLACED</textarea>			
		</div>'), $this->stripTabs($template->output()));
	}


	public function testDisplayNone() {
		$template = '
		<div>
			<a name="foo">a link</a>
			<textarea name="foo">foo</textarea>			
		</div>
		';

		$tss = 'textarea[name="foo"] {display: none;}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);
		$this->assertEquals($this->stripTabs('		<div>
			<a name="foo">a link</a>

		</div>'), $this->stripTabs($template->output()));
	}

	public function testBefore() {
		$template =  '
		<div>Test</div>
		';

		$tss = 'div:before {content: "BEFORE";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals($this->stripTabs('<div>BEFORETest</div>'), $this->stripTabs($template->output()));
	}

	public function testAfter() {
		$template =  '
		<div>Test</div>
		';

		$tss = 'div:after {content: "AFTER";}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals($this->stripTabs('<div>TestAFTER</div>'), $this->stripTabs($template->output()));
	}


	public function testIterationPseudo() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }
		ul li span:iteration[id="2"] {display: none;}
		';


		$template = new \Transphporm\Builder($template, $css, Builder::STRING);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
				<span>One</span>
			</li><li>
				<h2>2</h2>

			</li><li>
				<h2>3</h2>
				<span>Three</span>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)));

	}


	public function testMultiPseudo() {
		$data = new stdclass;
		$data->list = [];

		$one = new stdclass;		
		$one->name = 'One';
		$one->id = '1';
		$data->list[] = $one;

		$two = new stdclass;		
		$two->name = 'Two';
		$two->id = '2';
		$data->list[] = $two;

		$three = new stdclass;
		$three->name = 'Three';
		$three->id = '3';
		$data->list[] = $three;


		$template = '
				<ul>
					<li>
						<h2>header</h2>
						<span>TEST1</span>
					</li>
				</ul>
		';

		$css = 'ul li {repeat: data(list);}
		ul li h2 {content: iteration(id)}
		ul li span {content: iteration(name); }
		ul li span:iteration[id="2"]:before {content: "BEFORE";}
		';


		$template = new \Transphporm\Builder($template, $css, Builder::STRING);


		$this->assertEquals($this->stripTabs('<ul>
			<li>
				<h2>1</h2>
				<span>One</span>
			</li><li>
				<h2>2</h2>
				<span>BEFORETwo</span>				
			</li><li>
				<h2>3</h2>
				<span>Three</span>
			</li>			
		</ul>'), $this->stripTabs($template->output($data)));

	}

	public function testNthChild() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(2) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals($this->stripTabs('<ul>
				<li>One</li>
				<li>REPLACED</li>
				<li>Three</li>
				<li>Four</li>
			</ul>'), $this->stripTabs($template->output()));


	}

	public function testNthChildOdd() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(odd) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals($this->stripTabs('<ul>
				<li>REPLACED</li>
				<li>Two</li>
				<li>REPLACED</li>
				<li>Four</li>
			</ul>'), $this->stripTabs($template->output()));

	}

	public function testNthChildEven() {
		$template = '
			<ul>
				<li>One</li>
				<li>Two</li>
				<li>Three</li>
				<li>Four</li>
			</ul>
		';

		$tss = 'ul li:nth-child(even) {content: "REPLACED"}';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals($this->stripTabs('<ul>
				<li>One</li>
				<li>REPLACED</li>
				<li>Three</li>
				<li>REPLACED</li>
			</ul>'), $this->stripTabs($template->output()));

	}

	public function testReadAttribute() {
		$template = '
			<div class="fromattribute">Test</div>
		';

		$tss = 'div {content: attr(class); }';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals('<div class="fromattribute">fromattribute</div>', $template->output());
	}


	public function testWriteAttribute() {
		$template = '
			<div>Test</div>
		';

		$tss = 'div:attr(class) {content: "classname"; }';

		$template = new \Transphporm\Builder($template, $tss, Builder::STRING);

		$this->assertEquals('<div class="classname">Test</div>', $template->output());
	}
}


