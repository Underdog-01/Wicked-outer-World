<?php

/**
 * Handles starship designer rendering.
 *
 * Weapons
 * Ammunition
 * Equipment
 * Cargo
 * Engine
 */
class RenderHangarDesigner extends RenderHangarAbstract {
	/**
	 * @return string
	 */
	public function bodyHtml() {
		$this->moveItem();

		return $this->setupHtml();
	}

	/**
	 * @return string
	 */
	private function setupHtml() {
		$starship = $this->account()->starship();
		$stockage = $this->account()->stockage();

		$designerHtml = "
<colgroup>
	<col width='40'>
	<col>
	<col>
</colgroup>
<tr>
	<td colspan='2'>
		".i18n('yourStarship')."<br>
		<span class='highlight'>
			{$starship->name()}
			({$starship->weight()}t / {$starship->maxWeight()}t)
		</span>
	</td>
</tr>";

		$designerHtml .= plugins::itemList($stockage, $starship->weaponry(),	i18n('remove'));
		$designerHtml .= plugins::itemList($stockage, $starship->ammunition(),	i18n('remove'));
		$designerHtml .= plugins::itemList($stockage, $starship->equipment(),	i18n('remove'));
		$designerHtml .= plugins::itemList($stockage, $starship->cargo(),		i18n('remove'));
		$designerHtml .= plugins::itemList($stockage, $starship->engine(),		i18n('remove'));

		$designerHtml .= "
<tr>
	<td colspan='2' class='headline'>
			".i18n('yourStuff')."<br>
		<span class='highlight'>
			".$stockage->name()."
			(".$stockage->payload()."t / ".$stockage->tonnage()."t)
		</span>
	</td>
</tr>";

		$designerHtml .= plugins::itemList($starship, $stockage->stock(),			i18n('equip'), false);

		$howMany = i18n('howMany');
		JavaScript::create()->bind("$('.moveItem').moveItem('{$howMany}');");

		$designerHtml = html::defaultTable($designerHtml);
		$detailsHtml = $this->statusHtml();
		$detailsHtml = Plugins::box(
			i18n('yourShip'),
			$detailsHtml
		);

		return "
<h2>".i18n('garage')."</h2>
<p>".i18n('garageDescription')."</p>
<div class='floatRight columnRight'>{$detailsHtml}</div>
<div class='floatLeft columnLeft'>{$designerHtml}</div>
<div class='clear'></div>";
	}

	/**
	 * @return string
	 */
	private function statusHtml() {
		$starship = $this->account()->starship();

		$html = "
<colgroup>
	<col width='180'>
	<col width='80'>
</colgroup>
<tr>
	<td colspan='2' class='headline'>".i18n('weight')."</td>
</tr><tr>
	<td>".i18n('payload').":</td>
	<td class='variable right'>{$starship->payload()}t</td>
</tr><tr>
	<td>".i18n('tonnage').":</td>
	<td class='variable right'>{$starship->tonnage()}t</td>
</tr><tr>
	<td>".i18n('weight').":</td>
	<td class='variable right'>{$starship->weight()}t</td>
</tr><tr>
	<td colspan='2' class='headline'>".i18n('technicalData')."</td>
</tr><tr>
	<td>".i18n('possibleDamagePerRound').":</td>
	<td class='variable right'>".Format::number($starship->damagePerRound(), 1)."</td>
</tr><tr>
	<td>".i18n('internalStructure').":</td>
	<td class='variable right'>".Format::number($starship->structure(), 1)."</td>
</tr><tr>
	<td>".i18n('additionalArmor').":</td>
	<td class='variable right'>".Format::number($starship->armor(), 1)."</td>
</tr><tr>
	<td colspan='2' class='headline'>".i18n('energyManagement')."</td>
</tr><tr>
	<td>".i18n('drainPerRound').":</td>
	<td class='variable right'>".Format::number($starship->drainPerRound(), 1)."</td>
</tr><tr>
	<td>".i18n('rechargePerRound').":</td>
	<td class='variable right'>".Format::number($starship->rechargePerRound(), 1)."</td>
</tr><tr>
	<td>".i18n('capacity').":</td>
	<td class='variable right'>".Format::number($starship->capacity(), 1)."</td>
</tr>";

		return html::defaultTable($html);
	}

	/**
	 * @return void
	 */
	public function moveItem() {
		$request = Request::getInstance();

		$techId = $request->post('techId');
		$amount = (int)$request->post('amount');
		if (!$techId || $amount <= 0) {
			return;
		}

		$starship = $this->account()->starship();
		$stockage = $this->account()->stockage();

		$group = $request->post('slot');
		if ($starship->hasGroup($group)) {
			$techGroup = $starship->groupByName($group);

			if ($techGroup->hasItem($techId)) {
				$techGroup->moveItemTo(
					$stockage->stock(),
					$techGroup->item($techId),
					$amount
				);
			}
		}

		if ($stockage->hasGroup($group)) {
			$techGroup = $stockage->groupByName($group);

			if ($techGroup->hasItem($techId)) {
				$item = $techGroup->item($techId);

				$techGroup->moveItemTo(
					$starship->itemSlot($item),
					$item,
					$amount
				);
			}
		}
	}
}