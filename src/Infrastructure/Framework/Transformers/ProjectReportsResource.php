<?php

namespace Infrastructure\Framework\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectReportsResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param \Illuminate\Http\Request
   * @return array
   */
  public function toArray($request)
  {

    $data = $this->payload()->asArray();
    $eventData = $data['event_data'];
    unset($eventData['customer']['payment']['payment_method_token']);
    $hasCleaner = isset($eventData['cleaner']);
    $hasProject = isset($eventData['project']);
    $hasCustomer = isset($eventData['customer']);
    $hasRequest = isset($eventData['request']);
    return [
      '_type'               => 'ProjectReports',
      'id' => $this->getIdentifier()->id,
      'event_type' => $data['event_type'],
      'cleaner' => $this->when($hasCleaner, function () use ($eventData) {
        return $eventData['cleaner'];
      }),
      'project' => $this->when($hasProject, function () use ($eventData) {
        return $eventData['project'];
      }),
      'customer' => $this->when($hasCustomer, function () use ($eventData) {
        return $eventData['customer'];
      }),
      'request' => $this->when($hasRequest, function () use ($eventData) {
        return $eventData['request'];
      }),
    ];
  }
}
