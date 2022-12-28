<?php

namespace Infrastructure\Framework\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BasicProjectReportsResource extends JsonResource
{

  /**
   * The "data" wrapper that should be applied.
   *
   * @var string
   */
  public static $wrap = '';

  /**
   * Transform the resource into an array.
   *
   * @param \Illuminate\Http\Request
   * @return array
   */
  public function toArray($request)
  {

    $hasCancellationReason = $this['cancellation_reason'];
    return [
      '_type'               => 'ProjectReports',
      'state' => $this['state'],
      'cancellation_reason' => $this->when($hasCancellationReason, function () {
        return $this['cancellation_reason'];
      }),

    ];
  }
}
