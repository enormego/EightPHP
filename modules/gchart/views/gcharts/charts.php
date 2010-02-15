<h1>An Eight Module for the Google Chart API</h1>
<?php
$chart = new GChart_Pie;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");

?>
<h2>Pie Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Pie;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");</code>
	</td>
</tr></table>
<br />
<?php
$chart->set3D(true);
$chart->width = 400;
$chart->height = 150;
?>
<h2>3D Pie Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Pie;
$chart->set3D(true);
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");</code>
	</td>
</tr></table>
<br />
<?php
$chart = new GChart_Line;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
?>
<h2>Line Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Line;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");</code>
	</td>
</tr></table>
<br />
<?php
$chart = new GChart_Bar_Grouped;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
?>
<h2>Grouped Bar Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Bar_Grouped;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");</code>
	</td>
</tr></table>
<br />
<?php
$chart->setHorizontal(true);
$chart->valueLabels = array("first", "second", "third","fourth");
?>
<h2>Horizontal Grouped Bar Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Bar_Grouped;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
$chart->setHorizontal(true);
$chart->valueLabels = array("first", "second", "third","fourth");</code>
	</td>
</tr></table>
<br />
<?php
$chart = new GChart_Bar_Stacked;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
$chart->setTitle("Hey look, a title!");
?>
<h2>Stacked Bar Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Bar_Stacked;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
$chart->setTitle("Hey look, a title!");</code>
	</td>
</tr></table>
<br />
<?php
$chart->setHorizontal(true);
$chart->groupSpacerWidth = 10;
?>
<h2>Horizontal Stacked Bar Chart</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_Bar_Stacked;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addDataSet(array(212,115,366,140));
$chart->addDataSet(array(112,95,116,140));
$chart->valueLabels = array("first", "second", "third","fourth");
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
$chart->setTitle("Hey look, a title!");
$chart->setHorizontal(true);
$chart->groupSpacerWidth = 10;</code>
	</td>
</tr></table>
<br />
<?php
$chart = new GChart_VennDiagram;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addIntersections(array(22, 32, 4, 2));
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");
?>
<h2>Venn Diagram</h2>
<table style="width:100%"><tr>
	<td style="width:450px;text-align:center"><img src="<?php print $chart->getUrl();  ?>" /></td>
	<td style="width:auto">
		<code>$chart = new GChart_VennDiagram;
$chart->width = 400;
$chart->height = 150;
$chart->addDataSet(array(112,315,66,40));
$chart->addIntersections(array(22, 32, 4, 2));
$chart->dataColors = array("ff3344", "11ff11", "22aacc", "3333aa");</code>
	</td>
</tr></table>
<br />
