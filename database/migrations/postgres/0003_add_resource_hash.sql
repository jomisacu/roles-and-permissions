alter table _jomisacu_actor_permission_relations
    add column resource_hash char(64) not null default '';

update _jomisacu_actor_permission_relations
    set resource_hash = encode(sha256(resource::bytea), 'hex');

alter table _jomisacu_actor_permission_relations
    alter column resource_hash drop default;

drop index _jomisacu_actor_permission_relations_unique_rule;

create unique index _jomisacu_actor_permission_relations_unique_rule
    on _jomisacu_actor_permission_relations (context_id, actor_id, permission_id, negated, resource_hash);

alter table _jomisacu_role_permission_relations
    add column resource_hash char(64) not null default '';

update _jomisacu_role_permission_relations
    set resource_hash = encode(sha256(resource::bytea), 'hex');

alter table _jomisacu_role_permission_relations
    alter column resource_hash drop default;

drop index _jomisacu_role_permission_relations_unique_rule;

create unique index _jomisacu_role_permission_relations_unique_rule
    on _jomisacu_role_permission_relations (context_id, role_id, permission_id, negated, resource_hash);

