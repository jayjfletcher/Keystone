<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\DeleteChannelRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\IndexChannelsRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\ShowChannelRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\StoreChannelRequest;
use RefactorCircus\Keystone\Domains\Channel\Http\Requests\UpdateChannelRequest;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;

final class ChannelController
{
    public function index(IndexChannelsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreChannelRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowChannelRequest $request, ChannelModel $channel): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateChannelRequest $request, ChannelModel $channel): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteChannelRequest $request, ChannelModel $channel): JsonResponse
    {
        return $request->persist();
    }
}
