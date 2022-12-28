<?php

namespace Infrastructure\Framework\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderAddressAvailabilityResource extends JsonResource
{

  /**
   * Transform the resource into an array.
   *
   * @param \Illuminate\Http\Request
   * @return array
   */
  public function toArray($request)
  {
    return [
      '_type'               => 'Order',
      'order_id' => $this->order->getIdentifier()?->id,
      'order_accepted' => $this->order->getProjectId() > 0
    ];
  }
}
