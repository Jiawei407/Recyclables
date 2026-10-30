<?php
function metric($label) {
    global $uid,$role,$faculty;
    $label=strtolower($label);
    $where=''; $values=[];
    if ($role === 'user') { $where=' WHERE u.id=?'; $values=[$uid]; }
    if ($role === 'faculty') { $where=' WHERE u.faculty=?'; $values=[$faculty]; }
    if (str_contains($label,'popular')) {
        $order=str_contains($label,'least') ? 'ASC' : 'DESC';
        $row=one('SELECT c.title,COUNT(uc.user_id) AS total FROM challenges c LEFT JOIN user_challenges uc ON uc.challenge_id=c.id GROUP BY c.id ORDER BY total ' . $order . ',c.id LIMIT 1');
        return $row['title'] ?? '—';
    }
    if (str_contains($label,'completion rate')) {
        $join_count=one('SELECT COUNT(*) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id' . $where,$values)['total'];
        $complete_count=one("SELECT COUNT(DISTINCT s.user_id,s.challenge_id) AS total FROM submissions s JOIN users u ON u.id=s.user_id" . $where . ($where ? ' AND' : ' WHERE') . " s.status='approved'",$values)['total'];
        return ($join_count ? round(100*$complete_count/$join_count) : 0) . '%';
    }
    if (str_contains($label,'kg') || str_contains($label,'co2') || str_contains($label,'waste') || str_contains($label,'bottle') || str_contains($label,'plastic')) return 'Not measured';
    if (str_contains($label,'rank')) {
        $ranking=page_records('rankings');
        foreach ($ranking as $index=>$person) if ((int)$person['id'] === (int)$uid) return $index+1;
        return '—';
    }
    if (str_contains($label,'point')) return one('SELECT COALESCE(SUM(p.points),0) AS total FROM point_transactions p JOIN users u ON u.id=p.user_id' . $where,$values)['total'];
    if (str_contains($label,'facult')) return one('SELECT COUNT(DISTINCT faculty) AS total FROM users')['total'];
    if (str_contains($label,'badge') || str_contains($label,'achievement')) {
        $count=0;
        foreach (rows("SELECT id FROM achievements WHERE status='active'") as $badge) {
            if ($role !== 'user' || achievement_progress($badge['id'],$uid)['earned']) $count++;
        }
        return $count;
    }
    if (str_contains($label,'completed') || str_contains($label,'approved') || str_contains($label,'action')) {
        return one("SELECT COUNT(*) AS total FROM submissions s JOIN users u ON u.id=s.user_id" . $where . ($where ? ' AND' : ' WHERE') . " s.status='approved'",$values)['total'];
    }
    if (str_contains($label,'review') || str_contains($label,'pending') || str_contains($label,'submission')) {
        return one("SELECT COUNT(*) AS total FROM submissions s JOIN users u ON u.id=s.user_id" . $where . ($where ? ' AND' : ' WHERE') . " s.status='pending'",$values)['total'];
    }
    if (str_contains($label,'challenge')) {
        if ($role === 'user') return one('SELECT COUNT(*) AS total FROM user_challenges WHERE user_id=?',[$uid])['total'];
        return one("SELECT COUNT(*) AS total FROM challenges WHERE status='open' AND start_date<=CURDATE() AND end_date>=CURDATE()")['total'];
    }
    if (str_contains($label,'particip') || str_contains($label,'member') || str_contains($label,'user') || str_contains($label,'account')) return one('SELECT COUNT(*) AS total FROM users u' . $where . ($where ? ' AND' : ' WHERE') . " u.status='active' AND u.role='user'",$values)['total'];
    return '—';
}
