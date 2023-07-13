<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Connection;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    public function getSuggestions(Request $request)
    {
        $userId = $request->input('userId');

        $suggestedUsers = User::whereNotIn('id', function ($query) use ($userId) {
            $query->select('target_user_id')
                ->from('connections')
                ->where('user_id', $userId)
                ->orWhere('target_user_id', $userId);
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('user_id')
                    ->from('connections')
                    ->where('target_user_id', $userId)
                    ->where('status', 'pending');
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('target_user_id')
                    ->from('connections')
                    ->where('user_id', $userId)
                    ->where('status', 'pending');
            })
            ->where('id','!=', $userId)
            ->take(10)
            ->get();

        return response()->json($suggestedUsers);
    }
    public function getCount(Request $request)
    {
        $userId = $request->input('userId');

        $sentRequests = Connection::with('targetUser')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $receivedRequests = Connection::with('user')
            ->where('target_user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $suggestedUsers = User::whereNotIn('id', function ($query) use ($userId) {
            $query->select('target_user_id')
                ->from('connections')
                ->where('user_id', $userId)
                ->orWhere('target_user_id', $userId);
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('user_id')
                    ->from('connections')
                    ->where('target_user_id', $userId)
                    ->where('status', 'pending');
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('target_user_id')
                    ->from('connections')
                    ->where('user_id', $userId)
                    ->where('status', 'pending');
            })
            ->where('id','!=', $userId)
            ->count();

            $connections = Connection::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('target_user_id', $userId);
            })
            ->where('status', '=', 'accepted')
            ->with(['user', 'targetUser'])
            ->count();

            return [
                'suggestions'       => $suggestedUsers, 
                'sent-requests'     => $sentRequests, 
                'connections'       => $connections,
                'received-requests' => $receivedRequests
            ];
        
    }

    public function connect(Request $request)
    {
        $userId       = $request->input('user_id');
        $targetUserId = $request->input('target_user_id');

        $connection                 = new Connection();
        $connection->user_id        = $userId;
        $connection->target_user_id = $targetUserId;
        $connection->status         = 'pending';
        $connection->save();

        return response()->json('Connection request sent successfully');
    }

    public function getSentRequests(Request $request)
    {
        $userId = $request->input('userId');

        $sentRequests = Connection::with('targetUser')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->take(10)
            ->get();

        return response()->json($sentRequests);
    }

    public function withdrawRequest($connectionId)
    {
        $connection = Connection::find($connectionId);
        $connection->delete();

        return response()->json('Connection request withdrawn successfully');
    }

    public function getReceivedRequests(Request $request)
    {
        $userId = $request->input('userId');

        $receivedRequests = Connection::with('user')
            ->where('target_user_id', $userId)
            ->where('status', 'pending')
            ->take(10)
            ->get();

        return response()->json($receivedRequests);
    }

    public function acceptRequest($connectionId)
    {
        $connection = Connection::find($connectionId);
        $connection->status = 'accepted';
        $connection->save();

        return response()->json('Connection request accepted successfully');
    }


    public function removeConnection($connectionId)
    {
        $connection = Connection::find($connectionId);
        $connection->delete();

        return response()->json('Connection removed successfully');
    }

   
    
    public function getConnections(Request $request)
    {
        $userId = $request->input('userId');
        
        $connections = Connection::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('target_user_id', $userId);
        })
        ->where('status', '=', 'accepted')
        ->with(['user', 'targetUser'])
        ->take(10)
        ->get();

        $commonConnections = $this->getConnectionsInCommon($userId);
        $connections = $connections->map(function ($connection) use ($commonConnections) {
            $connectionUserId = $connection->user_id;
            $connectionTargetUserId = $connection->target_user_id;

            $commonConnection = $commonConnections->first(function ($common) use ($connectionUserId, $connectionTargetUserId) {
                return ($common['user_id'] === $connectionUserId && $common['connected_user_id'] === $connectionTargetUserId) ||
                    ($common['user_id'] === $connectionTargetUserId && $common['connected_user_id'] === $connectionUserId);
            });

            $connection->common_connections = $commonConnection ? $commonConnection['common_connections'] : 0;

            return $connection;
        });

        return response()->json($connections);
    }

    public function getConnectionsInCommon($userId)
    {    
        $loggedInUserConnections = Connection::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('target_user_id', $userId);
        })
            ->where('status', 'accepted')
            ->pluck('user_id', 'target_user_id')
            ->toArray();

        $userConnections = Connection::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('target_user_id', $userId);
        })
            ->where('status', 'accepted')
            ->pluck('user_id', 'target_user_id')
            ->toArray();

        $commonConnections = [];
        foreach ($loggedInUserConnections as $loggedInUserConnectionUserId => $loggedInUserConnectionTargetUserId) {
            if (isset($userConnections[$loggedInUserConnectionUserId]) || isset($userConnections[$loggedInUserConnectionTargetUserId])) {
                $count = isset($userConnections[$loggedInUserConnectionUserId]) ? 1 : 0;
                $count += isset($userConnections[$loggedInUserConnectionTargetUserId]) ? 1 : 0;
                $commonConnections[] = [
                    'user_id' => $loggedInUserConnectionUserId === $userId ? $loggedInUserConnectionTargetUserId : $loggedInUserConnectionUserId,
                    'connected_user_id' => $loggedInUserConnectionUserId === $userId ? $loggedInUserConnectionTargetUserId : $loggedInUserConnectionUserId,
                    'common_connections' => $count
                ];
            }
        }

        return collect($commonConnections);
    }

    public function loadMore(Request $request)
    {
        $type   = $request->input('type');
        $offset = $request->input('offset');
        $userId = $request->input('userId');

        switch ($type) {
            case 'suggestions':
                $data = User::whereNotIn('id', function ($query) use ($userId) {
                    $query->select('target_user_id')
                        ->from('connections')
                        ->where('user_id', $userId)
                        ->orWhere('target_user_id', $userId)
                        ->where('status', 'accepted');
                })
                ->where('id', '!=', $userId)
                ->skip($offset)
                ->take(10)
                ->get();
                break;
            case 'sent-requests':
                $data = Connection::with('targetUser')
                    ->where('user_id', $userId)
                    ->where('status', 'pending')
                    ->skip($offset)
                    ->take(10)
                    ->get();
                break;
            case 'received-requests':                
                $data = Connection::with('user')
                    ->where('target_user_id', $userId)
                    ->where('status', 'pending')
                    ->skip($offset)
                    ->take(10)
                    ->get();
                break;
            case 'connected':
                $data = Connection::where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhere('target_user_id', $userId);
                })
                ->where('status', '=', 'accepted')
                ->with(['user', 'targetUser'])
                ->skip($offset)
                ->take(10)
                ->get();
                break;
            default:
                $data = [];
        }

        return response()->json($data);
    }
    public function getConnectionsNameInCommon(Request $request)
    {
        $userId = $request->input('userId');
        $connectedUserId = $request->input('connectedUserId');
        $loggedInUserConnections = Connection::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('target_user_id', $userId);
        })
            ->where('status', 'accepted')
            ->pluck('user_id', 'target_user_id')
            ->toArray();

        $connectedUserConnections = Connection::where(function ($query) use ($connectedUserId) {
            $query->where('user_id', $connectedUserId)
                ->orWhere('target_user_id', $connectedUserId);
        })
            ->where('status', 'accepted')
            ->pluck('user_id', 'target_user_id')
            ->toArray();

        $commonUsers = [];
        foreach ($loggedInUserConnections as $loggedInUserConnectionUserId => $loggedInUserConnectionTargetUserId) {
            if (isset($connectedUserConnections[$loggedInUserConnectionUserId]) || isset($connectedUserConnections[$loggedInUserConnectionTargetUserId])) {
                if ($loggedInUserConnectionUserId !== $userId) {
                    $commonUser = User::find($loggedInUserConnectionUserId);
                    $commonUsers[] = $commonUser->name;
                }
                if ($loggedInUserConnectionTargetUserId !== $userId) {
                    $commonUser = User::find($loggedInUserConnectionTargetUserId);
                    $commonUsers[] = $commonUser->name;
                }
            }
        }

        return response()->json($commonUsers);
    }    
}
