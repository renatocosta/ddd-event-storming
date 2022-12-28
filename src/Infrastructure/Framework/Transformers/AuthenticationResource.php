<?php

namespace Infrastructure\Framework\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticationResource extends JsonResource
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
      '_type'               => 'Authentication'
    ];
  }
}
