alter table _jomisacu_actor_role_relations
    add column updated_at datetime null after updated_by_user_id;

create unique index _jomisacu_actor_permission_relations_unique_rule
    on _jomisacu_actor_permission_relations (context_id, actor_id, permission_id, negated, resource(191));

create unique index _jomisacu_actor_role_relations_unique_assignment
    on _jomisacu_actor_role_relations (context_id, role_id, actor_id);

create unique index _jomisacu_role_permission_relations_unique_rule
    on _jomisacu_role_permission_relations (context_id, role_id, permission_id, negated, resource(191));
