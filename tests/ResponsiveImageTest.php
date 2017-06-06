<?php

class ResponsiveImageTest extends SapphireTest {
	protected $imagePath     = null;
	protected $fullPath      = null;
	protected $image         = null;
	protected $needsDeletion = false;

	public function setUpOnce() {
		parent::setUpOnce();
		$this->imagePath = ASSETS_DIR . DIRECTORY_SEPARATOR . 'Lenna.jpg';
		$this->fullPath  = ASSETS_PATH . DIRECTORY_SEPARATOR . basename($this->imagePath);
		$sourcePath      = dirname(__FILE__) . DIRECTORY_SEPARATOR . basename($this->imagePath);

		if (!file_exists($this->fullPath)) {
			$this->needsDeletion = true;
			copy($sourcePath, $this->fullPath);
		}
	}

	public function tearDownOnce() {
		parent::tearDownOnce();

		if ($this->needsDeletion) {
			unlink($this->fullPath);
		}
	}

	public function setUp() {
		parent::setUp();

		$this->imagePath = ASSETS_DIR . DIRECTORY_SEPARATOR . 'Lenna.jpg';
		$this->fullPath  = ASSETS_PATH . DIRECTORY_SEPARATOR . basename($this->imagePath);
		$image           = new Image();
		$image->Filename = $this->imagePath;
		$image->write();
		$this->image = $image;

		Config::inst()->update('Image_Responsive', 'small_scaling_factor', 0.25);
		Config::inst()->update('Image_Responsive', 'medium_scaling_factor', 0.5);
	}

	public function testLargeSourceWidth() {
		$respImg = $this->image->Responsive();
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getLargeSourceWidth(), '485w', 'Expected large width to equal image original width');
	}

	public function testLargeSourceHeight() {
		$respImg = $this->image->Responsive();
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getLargeSourceHeight(), '328h', 'Expected large height to equal image original');
	}

	public function testDefaultMediumWidth() {
		$respImg = $this->image->Responsive();
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getMediumSourceWidth(), '243w', 'Expected medium width to equal image original width / 2');
	}

	public function testDefaultMediumHeight() {
		$respImg = $this->image->Responsive();
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getMediumSourceHeight(), '164h', 'Expected medium height to equal image original / 2');
	}

	public function testDefaultSmallWidth() {
		$respImg = $this->image->Responsive();
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getSmallSourceWidth(), '121w', 'Expected small width to equal image original width / 4');
	}

	public function testDefaultSmallHeight() {
		$respImg = $this->image->Responsive();
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getSmallSourceHeight(), '82h', 'Expected small height to equal image original / 4');
	}

	public function testMediaQuery() {
		$respImg = $this->image->Responsive('320w');
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getSmallSourceHeight(), '82h', 'Expected small height to equal image original / 4');
		$this->assertEquals($respImg->getMediaQuery(), '320w', 'Failed to retrieve expected media query');
	}

	public function testCropMethodSizes() {
		$respImg = $this->image->Responsive('320w', 'CroppedImage', 320, 200);
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getLargeSourceWidth(), '320w', 'Expected large width to equal passed in method width');
		$this->assertEquals($respImg->getMediumSourceWidth(), '160w', 'Expected medium width to equal passed in method width, adapted');
		$this->assertEquals($respImg->getSmallSourceWidth(), '80w', 'Expected small width to equal passed in method width, adapted');

		$this->assertEquals($respImg->getLargeSourceHeight(), '200h', 'Expected large height to equal passed in method height');
		$this->assertEquals($respImg->getMediumSourceHeight(), '100h', 'Expected medium height to equal passed in method height, adapted');
		$this->assertEquals($respImg->getSmallSourceHeight(), '50h', 'Expected small height to equal passed in method height, adapted');
	}

	public function testSetHeightSizes() {
		$respImg = $this->image->Responsive('320w', 'SetHeight', 200);
		$this->assertNotEquals($respImg, null, 'Responsive() returned null');
		$this->assertEquals($respImg->getLargeSourceWidth(), '295w', 'Expected large width to equal passed in method width');
		$this->assertEquals($respImg->getMediumSourceWidth(), '148w', 'Expected medium width to equal passed in method width, adapted');
		$this->assertEquals($respImg->getSmallSourceWidth(), '73w', 'Expected small width to equal passed in method width, adapted');

		$this->assertEquals($respImg->getLargeSourceHeight(), '200h', 'Expected large height to equal passed in method height');
		$this->assertEquals($respImg->getMediumSourceHeight(), '100h', 'Expected medium height to equal passed in method height, adapted');
		$this->assertEquals($respImg->getSmallSourceHeight(), '50h', 'Expected small height to equal passed in method height, adapted');
	}
}