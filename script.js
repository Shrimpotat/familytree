$(function(){
    const $search = $('#search');
    $search.on('input', function(){
        const q = $(this).val();
        $.getJSON('search.php',{q}, function(data){
            const $results = $('#results');
            $results.empty();
            data.forEach(p=>{
                const card = `<div class="card"><h3>${escapeHtml(p.first_name+' '+p.last_name)}</h3><p>${escapeHtml(p.bio||'')}</p><div class="actions"><a href="edit_person.php?id=${p.id}">Edit</a> <a href="delete_person.php?id=${p.id}">Delete</a> <a href="lineage.php?id=${p.id}">Lineage</a></div></div>`;
                $results.append(card);
            });
        });
    });
});
function escapeHtml(str){ if(!str) return ''; return String(str).replace(/[&<>"']/g,function(s){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]}); }
