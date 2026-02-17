<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Requests;

use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Request;

class HeaderNoticeBarRequest
{
    /** @var Request */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function postBanner()
    {
        $banner = new \stdClass;
        $banner->id = $this->request->post('id', 'integer');
        $name = (string)$this->request->post('name');
        $banner->name = $name !== '' ? substr($name, 0, 255) : '';
        $banner->content = $this->request->post('content') ?: '';
        $rawType = $this->request->post('background_type', 'string');
        $banner->background_type = ($rawType === 'gradient' || $rawType === 'color') ? $rawType : 'color';
        
        $backgroundColor = $this->sanitizeCssValue(trim((string)$this->request->post('background_color')));
        $banner->background_color = $backgroundColor !== '' ? $backgroundColor : '#ffffff';
        
        $customGradient = $this->request->post('background_gradient');
        $customGradient = $customGradient !== null && $customGradient !== '' ? $this->sanitizeCssValue(trim((string)$customGradient)) : null;
        $gradientColorFrom = $this->request->post('gradient_color_from');
        $gradientColorFrom = $gradientColorFrom !== null && $gradientColorFrom !== '' ? $this->sanitizeCssValue(trim((string)$gradientColorFrom)) : null;
        $gradientColorTo = $this->request->post('gradient_color_to');
        $gradientColorTo = $gradientColorTo !== null && $gradientColorTo !== '' ? $this->sanitizeCssValue(trim((string)$gradientColorTo)) : null;
        
        if ($banner->background_type === 'gradient' && !empty($customGradient)) {
            $banner->background_gradient = $customGradient;
            $banner->gradient_color_from = null;
            $banner->gradient_color_to = null;
        } elseif ($banner->background_type === 'gradient' && !empty($gradientColorFrom) && !empty($gradientColorTo)) {
            $banner->background_gradient = null;
            $banner->gradient_color_from = $gradientColorFrom;
            $banner->gradient_color_to = $gradientColorTo;
        } else {
            $banner->background_gradient = null;
            $banner->gradient_color_from = null;
            $banner->gradient_color_to = null;
        }
        
        $banner->visible = $this->request->post('visible', 'boolean') ? 1 : 0;
        $banner->position = $this->request->post('position', 'integer') ?: 0;
        
        $publishFrom = $this->request->post('publish_from', 'string');
        $publishTo = $this->request->post('publish_to', 'string');

        $banner->publish_from = $this->parseDatetime($publishFrom);
        $banner->publish_to = $this->parseDatetime($publishTo);

        return ExtenderFacade::execute(__METHOD__, $banner, func_get_args());
    }

    public function postCheck()
    {
        $check = (array) $this->request->post('check');
        return ExtenderFacade::execute(__METHOD__, $check, func_get_args());
    }

    public function postAction()
    {
        $action = $this->request->post('action');
        return ExtenderFacade::execute(__METHOD__, $action, func_get_args());
    }

    public function postPositions()
    {
        $positions = $this->request->post('positions');
        return ExtenderFacade::execute(__METHOD__, $positions, func_get_args());
    }

    private function sanitizeCssValue($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }
        $value = preg_replace('/["\'<>\\\\\\x00-\\x1f]/', '', $value);
        return strlen($value) > 500 ? substr($value, 0, 500) : $value;
    }

    private function parseDatetime($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }
}
