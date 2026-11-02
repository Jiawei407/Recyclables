<?php
/** Read the participant's real dashboard records, using the same achievement rules as the badge pages. */
function participant_dashboard(int $user_id): array
{
    $active=one("SELECT c.* FROM user_challenges uc JOIN challenges c ON c.id=uc.challenge_id WHERE uc.user_id=? AND c.status='open' AND c.start_date<=CURDATE() AND c.end_date>=CURDATE() ORDER BY uc.joined_at DESC,c.id DESC LIMIT 1",[$user_id]);
    $milestone=null;
    foreach (rows("SELECT * FROM achievements WHERE status='active' ORDER BY id") as $badge) {
        $progress=achievement_progress($badge['id'],$user_id);
        if (!$progress['earned'] && $progress['rules']) {
            $milestone=['badge'=>$badge,'rules'=>$progress['rules']];
            break;
        }
    }
    $activity=rows("SELECT 'submission' AS kind,s.id,c.title,s.status,0 AS points,COALESCE(s.reviewed_at,s.created_at) AS occurred_at FROM submissions s JOIN challenges c ON c.id=s.challenge_id WHERE s.user_id=?
        UNION ALL SELECT 'points',p.id,p.reason,'',p.points,p.created_at FROM point_transactions p WHERE p.user_id=?
        UNION ALL SELECT 'joined',c.id,c.title,'',0,uc.joined_at FROM user_challenges uc JOIN challenges c ON c.id=uc.challenge_id WHERE uc.user_id=?
        ORDER BY occurred_at DESC,id DESC,kind LIMIT 5",[$user_id,$user_id,$user_id]);
    return ['active_challenge'=>$active,'milestone'=>$milestone,'activity'=>$activity];
}
