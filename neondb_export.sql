--
-- PostgreSQL database dump
--

\restrict igpKSQ6U4dTDuPSaeD6oXrm5OHLQGXb4b4od1NCZa9L9T9a3PRXKIb95SappelW

-- Dumped from database version 17.7 (bdc8956)
-- Dumped by pg_dump version 18.1 (Debian 18.1-1)

-- Started on 2026-01-01 09:41:12 WAT

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 6 (class 2615 OID 24576)
-- Name: neon_auth; Type: SCHEMA; Schema: -; Owner: neondb_owner
--

CREATE SCHEMA neon_auth;


ALTER SCHEMA neon_auth OWNER TO neondb_owner;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 218 (class 1259 OID 24577)
-- Name: users_sync; Type: TABLE; Schema: neon_auth; Owner: neondb_owner
--

CREATE TABLE neon_auth.users_sync (
    raw_json jsonb NOT NULL,
    id text GENERATED ALWAYS AS ((raw_json ->> 'id'::text)) STORED NOT NULL,
    name text GENERATED ALWAYS AS ((raw_json ->> 'display_name'::text)) STORED,
    email text GENERATED ALWAYS AS ((raw_json ->> 'primary_email'::text)) STORED,
    created_at timestamp with time zone GENERATED ALWAYS AS (to_timestamp((trunc((((raw_json ->> 'signed_up_at_millis'::text))::bigint)::double precision) / (1000)::double precision))) STORED,
    updated_at timestamp with time zone,
    deleted_at timestamp with time zone
);


ALTER TABLE neon_auth.users_sync OWNER TO neondb_owner;

--
-- TOC entry 235 (class 1259 OID 32857)
-- Name: academic_years; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.academic_years (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    active boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.academic_years OWNER TO neondb_owner;

--
-- TOC entry 234 (class 1259 OID 32856)
-- Name: academic_years_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.academic_years_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.academic_years_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3738 (class 0 OID 0)
-- Dependencies: 234
-- Name: academic_years_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.academic_years_id_seq OWNED BY public.academic_years.id;


--
-- TOC entry 225 (class 1259 OID 32802)
-- Name: cache; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO neondb_owner;

--
-- TOC entry 226 (class 1259 OID 32809)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO neondb_owner;

--
-- TOC entry 273 (class 1259 OID 253996)
-- Name: cahier_de_texte; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.cahier_de_texte (
    id bigint NOT NULL,
    class_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    teacher_id bigint NOT NULL,
    timetable_id bigint NOT NULL,
    day character varying(255) NOT NULL,
    content text NOT NULL,
    academic_year_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    motif_retard text,
    duration_minutes integer DEFAULT 0 NOT NULL,
    is_late boolean DEFAULT false NOT NULL
);


ALTER TABLE public.cahier_de_texte OWNER TO neondb_owner;

--
-- TOC entry 272 (class 1259 OID 253995)
-- Name: cahier_de_texte_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.cahier_de_texte_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cahier_de_texte_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3739 (class 0 OID 0)
-- Dependencies: 272
-- Name: cahier_de_texte_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.cahier_de_texte_id_seq OWNED BY public.cahier_de_texte.id;


--
-- TOC entry 251 (class 1259 OID 32995)
-- Name: class_teacher_subject; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.class_teacher_subject (
    id bigint NOT NULL,
    class_id bigint NOT NULL,
    teacher_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    academic_year_id bigint,
    coefficient integer DEFAULT 1,
    amount_brut numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    CONSTRAINT class_teacher_subject_coefficient_check CHECK (((coefficient >= 1) AND (coefficient <= 10)))
);


ALTER TABLE public.class_teacher_subject OWNER TO neondb_owner;

--
-- TOC entry 250 (class 1259 OID 32994)
-- Name: class_teacher_subject_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.class_teacher_subject_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.class_teacher_subject_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3740 (class 0 OID 0)
-- Dependencies: 250
-- Name: class_teacher_subject_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.class_teacher_subject_id_seq OWNED BY public.class_teacher_subject.id;


--
-- TOC entry 239 (class 1259 OID 32876)
-- Name: classes; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.classes (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    entity_id bigint NOT NULL,
    academic_year_id bigint NOT NULL,
    teacher_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    school_fees numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    description text
);


ALTER TABLE public.classes OWNER TO neondb_owner;

--
-- TOC entry 238 (class 1259 OID 32875)
-- Name: classes_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.classes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.classes_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3741 (class 0 OID 0)
-- Dependencies: 238
-- Name: classes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.classes_id_seq OWNED BY public.classes.id;


--
-- TOC entry 259 (class 1259 OID 41015)
-- Name: conducts; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.conducts (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    academic_year_id bigint NOT NULL,
    entity_id bigint NOT NULL,
    grade character varying(255) DEFAULT 'A'::character varying NOT NULL,
    comment text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.conducts OWNER TO neondb_owner;

--
-- TOC entry 258 (class 1259 OID 41014)
-- Name: conducts_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.conducts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.conducts_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3742 (class 0 OID 0)
-- Dependencies: 258
-- Name: conducts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.conducts_id_seq OWNED BY public.conducts.id;


--
-- TOC entry 245 (class 1259 OID 32916)
-- Name: enrollments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.enrollments (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    class_id bigint NOT NULL,
    academic_year_id bigint NOT NULL,
    status character varying(255) DEFAULT 'enrolled'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.enrollments OWNER TO neondb_owner;

--
-- TOC entry 244 (class 1259 OID 32915)
-- Name: enrollments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.enrollments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.enrollments_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3743 (class 0 OID 0)
-- Dependencies: 244
-- Name: enrollments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.enrollments_id_seq OWNED BY public.enrollments.id;


--
-- TOC entry 237 (class 1259 OID 32865)
-- Name: entities; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.entities (
    id bigint NOT NULL,
    slug character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.entities OWNER TO neondb_owner;

--
-- TOC entry 236 (class 1259 OID 32864)
-- Name: entities_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.entities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.entities_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3744 (class 0 OID 0)
-- Dependencies: 236
-- Name: entities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.entities_id_seq OWNED BY public.entities.id;


--
-- TOC entry 231 (class 1259 OID 32834)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO neondb_owner;

--
-- TOC entry 230 (class 1259 OID 32833)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3745 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 263 (class 1259 OID 41073)
-- Name: grades; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.grades (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    value numeric(5,2) NOT NULL,
    trimestre smallint NOT NULL,
    sequence integer,
    academic_year_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    class_id bigint,
    CONSTRAINT grades_type_check CHECK (((type)::text = ANY ((ARRAY['interrogation'::character varying, 'devoir'::character varying])::text[])))
);


ALTER TABLE public.grades OWNER TO neondb_owner;

--
-- TOC entry 262 (class 1259 OID 41072)
-- Name: grades_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.grades_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.grades_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3746 (class 0 OID 0)
-- Dependencies: 262
-- Name: grades_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.grades_id_seq OWNED BY public.grades.id;


--
-- TOC entry 247 (class 1259 OID 32940)
-- Name: invitations; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.invitations (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    invited_by bigint NOT NULL,
    role_id bigint,
    academic_year_id bigint,
    token character varying(255) NOT NULL,
    used_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    entity character varying(255) NOT NULL
);


ALTER TABLE public.invitations OWNER TO neondb_owner;

--
-- TOC entry 246 (class 1259 OID 32939)
-- Name: invitations_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.invitations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.invitations_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3747 (class 0 OID 0)
-- Dependencies: 246
-- Name: invitations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.invitations_id_seq OWNED BY public.invitations.id;


--
-- TOC entry 229 (class 1259 OID 32826)
-- Name: job_batches; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO neondb_owner;

--
-- TOC entry 228 (class 1259 OID 32817)
-- Name: jobs; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO neondb_owner;

--
-- TOC entry 227 (class 1259 OID 32816)
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3748 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- TOC entry 220 (class 1259 OID 32769)
-- Name: migrations; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO neondb_owner;

--
-- TOC entry 219 (class 1259 OID 32768)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3749 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 275 (class 1259 OID 409605)
-- Name: note_edit_permissions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.note_edit_permissions (
    id bigint NOT NULL,
    teacher_id bigint NOT NULL,
    class_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    academic_year_id bigint NOT NULL,
    trimestre character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.note_edit_permissions OWNER TO neondb_owner;

--
-- TOC entry 274 (class 1259 OID 409604)
-- Name: note_edit_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.note_edit_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.note_edit_permissions_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3750 (class 0 OID 0)
-- Dependencies: 274
-- Name: note_edit_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.note_edit_permissions_id_seq OWNED BY public.note_edit_permissions.id;


--
-- TOC entry 271 (class 1259 OID 41153)
-- Name: note_permissions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.note_permissions (
    id bigint NOT NULL,
    class_id bigint NOT NULL,
    trimestre smallint NOT NULL,
    is_open boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    open_at timestamp(0) without time zone,
    close_at timestamp(0) without time zone
);


ALTER TABLE public.note_permissions OWNER TO neondb_owner;

--
-- TOC entry 270 (class 1259 OID 41152)
-- Name: note_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.note_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.note_permissions_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3751 (class 0 OID 0)
-- Dependencies: 270
-- Name: note_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.note_permissions_id_seq OWNED BY public.note_permissions.id;


--
-- TOC entry 223 (class 1259 OID 32786)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO neondb_owner;

--
-- TOC entry 257 (class 1259 OID 40991)
-- Name: punishments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.punishments (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    academic_year_id bigint NOT NULL,
    entity_id bigint NOT NULL,
    reason text NOT NULL,
    date_punishment date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    hours integer DEFAULT 1 NOT NULL
);


ALTER TABLE public.punishments OWNER TO neondb_owner;

--
-- TOC entry 256 (class 1259 OID 40990)
-- Name: punishments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.punishments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.punishments_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3752 (class 0 OID 0)
-- Dependencies: 256
-- Name: punishments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.punishments_id_seq OWNED BY public.punishments.id;


--
-- TOC entry 233 (class 1259 OID 32846)
-- Name: roles; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO neondb_owner;

--
-- TOC entry 232 (class 1259 OID 32845)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3753 (class 0 OID 0)
-- Dependencies: 232
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 261 (class 1259 OID 41051)
-- Name: schedules; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.schedules (
    id bigint NOT NULL,
    classe_id bigint NOT NULL,
    teacher_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    day_of_week character varying(255) NOT NULL,
    start_time time(0) without time zone NOT NULL,
    end_time time(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.schedules OWNER TO neondb_owner;

--
-- TOC entry 260 (class 1259 OID 41050)
-- Name: schedules_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.schedules_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3754 (class 0 OID 0)
-- Dependencies: 260
-- Name: schedules_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.schedules_id_seq OWNED BY public.schedules.id;


--
-- TOC entry 224 (class 1259 OID 32793)
-- Name: sessions; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO neondb_owner;

--
-- TOC entry 269 (class 1259 OID 41135)
-- Name: student_annual_averages; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.student_annual_averages (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    average numeric(5,2) NOT NULL,
    rank integer,
    academic_year_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.student_annual_averages OWNER TO neondb_owner;

--
-- TOC entry 268 (class 1259 OID 41134)
-- Name: student_annual_averages_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.student_annual_averages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.student_annual_averages_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3755 (class 0 OID 0)
-- Dependencies: 268
-- Name: student_annual_averages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.student_annual_averages_id_seq OWNED BY public.student_annual_averages.id;


--
-- TOC entry 255 (class 1259 OID 33043)
-- Name: student_payments; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.student_payments (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    tranche integer NOT NULL,
    amount numeric(10,2) NOT NULL,
    payment_date date NOT NULL,
    receipt character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    academic_year_id bigint
);


ALTER TABLE public.student_payments OWNER TO neondb_owner;

--
-- TOC entry 3756 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN student_payments.tranche; Type: COMMENT; Schema: public; Owner: neondb_owner
--

COMMENT ON COLUMN public.student_payments.tranche IS 'Numéro de tranche, 1, 2 ou 3';


--
-- TOC entry 3757 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN student_payments.receipt; Type: COMMENT; Schema: public; Owner: neondb_owner
--

COMMENT ON COLUMN public.student_payments.receipt IS 'Nom du fichier PDF du reçu';


--
-- TOC entry 254 (class 1259 OID 33042)
-- Name: student_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.student_payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.student_payments_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3758 (class 0 OID 0)
-- Dependencies: 254
-- Name: student_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.student_payments_id_seq OWNED BY public.student_payments.id;


--
-- TOC entry 267 (class 1259 OID 41118)
-- Name: student_trimestre_averages; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.student_trimestre_averages (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    trimestre smallint NOT NULL,
    average numeric(5,2) NOT NULL,
    rank integer,
    academic_year_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.student_trimestre_averages OWNER TO neondb_owner;

--
-- TOC entry 266 (class 1259 OID 41117)
-- Name: student_trimestre_averages_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.student_trimestre_averages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.student_trimestre_averages_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3759 (class 0 OID 0)
-- Dependencies: 266
-- Name: student_trimestre_averages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.student_trimestre_averages_id_seq OWNED BY public.student_trimestre_averages.id;


--
-- TOC entry 243 (class 1259 OID 32905)
-- Name: students; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.students (
    id bigint NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    birthdate date,
    registration_number character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    gender character varying(255) DEFAULT 'M'::character varying NOT NULL,
    num_educ character varying(255) NOT NULL,
    birth_place character varying(255),
    parent_phone character varying(255),
    school_fees_paid numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    fully_paid boolean DEFAULT false NOT NULL,
    validated boolean DEFAULT false NOT NULL,
    registration_fee numeric(10,2),
    is_validated boolean DEFAULT false NOT NULL,
    amount_paid numeric(10,2),
    school_fees numeric(10,2),
    academic_year_id bigint,
    birth_date date NOT NULL,
    entity_id smallint NOT NULL,
    class_id bigint,
    birth_certificate character varying(255),
    vaccination_card character varying(255),
    previous_report_card character varying(255),
    diploma_certificate character varying(255),
    parent_full_name character varying(255),
    parent_email character varying(255),
    age numeric(5,2) NOT NULL,
    CONSTRAINT students_gender_check CHECK (((gender)::text = ANY ((ARRAY['M'::character varying, 'F'::character varying])::text[])))
);


ALTER TABLE public.students OWNER TO neondb_owner;

--
-- TOC entry 242 (class 1259 OID 32904)
-- Name: students_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.students_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.students_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3760 (class 0 OID 0)
-- Dependencies: 242
-- Name: students_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.students_id_seq OWNED BY public.students.id;


--
-- TOC entry 265 (class 1259 OID 41096)
-- Name: subject_averages; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.subject_averages (
    id bigint NOT NULL,
    student_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    average numeric(5,2) NOT NULL,
    weighted_average numeric(5,2) NOT NULL,
    trimestre smallint NOT NULL,
    rank integer,
    academic_year_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.subject_averages OWNER TO neondb_owner;

--
-- TOC entry 264 (class 1259 OID 41095)
-- Name: subject_averages_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.subject_averages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.subject_averages_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3761 (class 0 OID 0)
-- Dependencies: 264
-- Name: subject_averages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.subject_averages_id_seq OWNED BY public.subject_averages.id;


--
-- TOC entry 241 (class 1259 OID 32898)
-- Name: subjects; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.subjects (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    academic_year_id bigint,
    classe_id bigint,
    coefficient integer DEFAULT 1 NOT NULL
);


ALTER TABLE public.subjects OWNER TO neondb_owner;

--
-- TOC entry 240 (class 1259 OID 32897)
-- Name: subjects_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.subjects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.subjects_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3762 (class 0 OID 0)
-- Dependencies: 240
-- Name: subjects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.subjects_id_seq OWNED BY public.subjects.id;


--
-- TOC entry 249 (class 1259 OID 32975)
-- Name: teacher_invitations; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.teacher_invitations (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    censeur_id bigint NOT NULL,
    token character varying(255) NOT NULL,
    accepted boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    accepted_at timestamp(0) without time zone,
    academic_year_id bigint,
    classe_id bigint
);


ALTER TABLE public.teacher_invitations OWNER TO neondb_owner;

--
-- TOC entry 248 (class 1259 OID 32974)
-- Name: teacher_invitations_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.teacher_invitations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.teacher_invitations_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3763 (class 0 OID 0)
-- Dependencies: 248
-- Name: teacher_invitations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.teacher_invitations_id_seq OWNED BY public.teacher_invitations.id;


--
-- TOC entry 253 (class 1259 OID 33019)
-- Name: timetables; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.timetables (
    id bigint NOT NULL,
    class_id bigint NOT NULL,
    teacher_id bigint NOT NULL,
    subject_id bigint NOT NULL,
    day character varying(255) NOT NULL,
    start_time time(0) without time zone NOT NULL,
    end_time time(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    academic_year_id bigint
);


ALTER TABLE public.timetables OWNER TO neondb_owner;

--
-- TOC entry 252 (class 1259 OID 33018)
-- Name: timetables_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.timetables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.timetables_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3764 (class 0 OID 0)
-- Dependencies: 252
-- Name: timetables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.timetables_id_seq OWNED BY public.timetables.id;


--
-- TOC entry 222 (class 1259 OID 32776)
-- Name: users; Type: TABLE; Schema: public; Owner: neondb_owner
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    role_id bigint,
    profile_photo character varying(255),
    gender character varying(255),
    phone character varying(255),
    marital_status character varying(255),
    address character varying(255),
    birth_date date,
    birth_place character varying(255),
    nationality character varying(255),
    id_card character varying(255),
    birth_certificate character varying(255),
    diploma character varying(255),
    ifu_number character varying(255),
    ifu character varying(255),
    rib character varying(255),
    rib_document character varying(255),
    id_card_file character varying(255),
    birth_certificate_file character varying(255),
    diploma_file character varying(255),
    ifu_file character varying(255),
    rib_file character varying(255),
    id_card_number character varying(255)
);


ALTER TABLE public.users OWNER TO neondb_owner;

--
-- TOC entry 221 (class 1259 OID 32775)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: neondb_owner
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO neondb_owner;

--
-- TOC entry 3765 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: neondb_owner
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 3347 (class 2604 OID 32860)
-- Name: academic_years id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.academic_years ALTER COLUMN id SET DEFAULT nextval('public.academic_years_id_seq'::regclass);


--
-- TOC entry 3381 (class 2604 OID 253999)
-- Name: cahier_de_texte id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte ALTER COLUMN id SET DEFAULT nextval('public.cahier_de_texte_id_seq'::regclass);


--
-- TOC entry 3365 (class 2604 OID 32998)
-- Name: class_teacher_subject id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject ALTER COLUMN id SET DEFAULT nextval('public.class_teacher_subject_id_seq'::regclass);


--
-- TOC entry 3350 (class 2604 OID 32879)
-- Name: classes id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.classes ALTER COLUMN id SET DEFAULT nextval('public.classes_id_seq'::regclass);


--
-- TOC entry 3372 (class 2604 OID 41018)
-- Name: conducts id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.conducts ALTER COLUMN id SET DEFAULT nextval('public.conducts_id_seq'::regclass);


--
-- TOC entry 3360 (class 2604 OID 32919)
-- Name: enrollments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.enrollments ALTER COLUMN id SET DEFAULT nextval('public.enrollments_id_seq'::regclass);


--
-- TOC entry 3349 (class 2604 OID 32868)
-- Name: entities id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.entities ALTER COLUMN id SET DEFAULT nextval('public.entities_id_seq'::regclass);


--
-- TOC entry 3344 (class 2604 OID 32837)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 3375 (class 2604 OID 41076)
-- Name: grades id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grades ALTER COLUMN id SET DEFAULT nextval('public.grades_id_seq'::regclass);


--
-- TOC entry 3362 (class 2604 OID 32943)
-- Name: invitations id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.invitations ALTER COLUMN id SET DEFAULT nextval('public.invitations_id_seq'::regclass);


--
-- TOC entry 3343 (class 2604 OID 32820)
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- TOC entry 3341 (class 2604 OID 32772)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 3384 (class 2604 OID 409608)
-- Name: note_edit_permissions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_edit_permissions ALTER COLUMN id SET DEFAULT nextval('public.note_edit_permissions_id_seq'::regclass);


--
-- TOC entry 3379 (class 2604 OID 41156)
-- Name: note_permissions id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_permissions ALTER COLUMN id SET DEFAULT nextval('public.note_permissions_id_seq'::regclass);


--
-- TOC entry 3370 (class 2604 OID 40994)
-- Name: punishments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.punishments ALTER COLUMN id SET DEFAULT nextval('public.punishments_id_seq'::regclass);


--
-- TOC entry 3346 (class 2604 OID 32849)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 3374 (class 2604 OID 41054)
-- Name: schedules id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.schedules ALTER COLUMN id SET DEFAULT nextval('public.schedules_id_seq'::regclass);


--
-- TOC entry 3378 (class 2604 OID 41138)
-- Name: student_annual_averages id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_annual_averages ALTER COLUMN id SET DEFAULT nextval('public.student_annual_averages_id_seq'::regclass);


--
-- TOC entry 3369 (class 2604 OID 33046)
-- Name: student_payments id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_payments ALTER COLUMN id SET DEFAULT nextval('public.student_payments_id_seq'::regclass);


--
-- TOC entry 3377 (class 2604 OID 41121)
-- Name: student_trimestre_averages id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_trimestre_averages ALTER COLUMN id SET DEFAULT nextval('public.student_trimestre_averages_id_seq'::regclass);


--
-- TOC entry 3354 (class 2604 OID 32908)
-- Name: students id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.students ALTER COLUMN id SET DEFAULT nextval('public.students_id_seq'::regclass);


--
-- TOC entry 3376 (class 2604 OID 41099)
-- Name: subject_averages id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subject_averages ALTER COLUMN id SET DEFAULT nextval('public.subject_averages_id_seq'::regclass);


--
-- TOC entry 3352 (class 2604 OID 32901)
-- Name: subjects id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subjects ALTER COLUMN id SET DEFAULT nextval('public.subjects_id_seq'::regclass);


--
-- TOC entry 3363 (class 2604 OID 32978)
-- Name: teacher_invitations id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations ALTER COLUMN id SET DEFAULT nextval('public.teacher_invitations_id_seq'::regclass);


--
-- TOC entry 3368 (class 2604 OID 33022)
-- Name: timetables id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.timetables ALTER COLUMN id SET DEFAULT nextval('public.timetables_id_seq'::regclass);


--
-- TOC entry 3342 (class 2604 OID 32779)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 3675 (class 0 OID 24577)
-- Dependencies: 218
-- Data for Name: users_sync; Type: TABLE DATA; Schema: neon_auth; Owner: neondb_owner
--

COPY neon_auth.users_sync (raw_json, updated_at, deleted_at) FROM stdin;
{"id": "1cc77416-6435-4a99-b89b-ea8e71e08a8c", "display_name": null, "has_password": false, "is_anonymous": false, "primary_email": "dondiegue21@gmail.com", "selected_team": null, "auth_with_email": false, "client_metadata": null, "oauth_providers": [], "server_metadata": null, "otp_auth_enabled": false, "selected_team_id": null, "profile_image_url": null, "requires_totp_mfa": false, "signed_up_at_millis": 1759611793612, "passkey_auth_enabled": false, "last_active_at_millis": 1759611793612, "primary_email_verified": false, "client_read_only_metadata": null, "primary_email_auth_enabled": true}	2025-10-04 21:03:13+00	\N
\.


--
-- TOC entry 3692 (class 0 OID 32857)
-- Dependencies: 235
-- Data for Name: academic_years; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.academic_years (id, name, active, created_at, updated_at) FROM stdin;
1	2025-2026	t	2025-10-04 22:53:59	2025-10-05 08:30:58
\.


--
-- TOC entry 3682 (class 0 OID 32802)
-- Dependencies: 225
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.cache (key, value, expiration) FROM stdin;
cpeg-marie-alain-cache-edkingslim@gmail.com|::1:timer	i:1764168036;	1764168037
cpeg-marie-alain-cache-edkingslim@gmail.com|::1	i:0;	1764168037
\.


--
-- TOC entry 3683 (class 0 OID 32809)
-- Dependencies: 226
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 3730 (class 0 OID 253996)
-- Dependencies: 273
-- Data for Name: cahier_de_texte; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.cahier_de_texte (id, class_id, subject_id, teacher_id, timetable_id, day, content, academic_year_id, created_at, updated_at, motif_retard, duration_minutes, is_late) FROM stdin;
\.


--
-- TOC entry 3708 (class 0 OID 32995)
-- Dependencies: 251
-- Data for Name: class_teacher_subject; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.class_teacher_subject (id, class_id, teacher_id, subject_id, created_at, updated_at, academic_year_id, coefficient, amount_brut) FROM stdin;
81	4	86	4	2025-11-18 16:45:35	2025-11-18 16:45:35	1	1	0.00
73	6	44	4	2025-11-17 14:52:10	2025-11-17 14:52:10	1	1	0.00
83	23	81	5	2025-11-19 07:43:22	2025-11-19 07:43:22	1	1	0.00
84	23	83	3	2025-11-19 07:44:12	2025-11-19 07:44:12	1	1	0.00
85	23	80	4	2025-11-19 07:49:57	2025-11-19 07:49:57	1	1	0.00
88	23	79	11	2025-11-19 07:52:57	2025-11-19 07:52:57	1	1	0.00
80	7	40	4	2025-11-17 15:16:07	2025-11-17 15:16:07	1	1	0.00
5	3	43	3	2025-11-14 13:35:58	2025-11-14 13:35:58	1	1	0.00
10	3	58	5	2025-11-14 13:42:11	2025-11-14 13:42:11	1	1	0.00
11	5	38	2	2025-11-14 13:46:48	2025-11-14 13:46:48	1	1	0.00
95	23	80	13	2025-11-19 08:09:11	2025-11-19 08:09:11	1	1	0.00
96	23	61	8	2025-11-19 08:11:28	2025-11-19 08:11:28	1	1	0.00
97	3	74	1	2025-11-19 08:14:38	2025-11-19 08:14:38	1	1	0.00
17	4	40	2	2025-11-14 14:05:48	2025-11-14 14:05:48	1	1	0.00
18	4	57	5	2025-11-14 14:06:48	2025-11-14 14:06:48	1	1	0.00
20	4	65	8	2025-11-14 14:08:44	2025-11-14 14:08:44	1	1	0.00
99	5	73	1	2025-11-19 08:18:09	2025-11-19 08:18:09	1	1	0.00
24	4	48	1	2025-11-14 14:11:50	2025-11-14 14:11:50	1	1	0.00
101	5	78	5	2025-11-19 08:28:17	2025-11-19 08:28:17	1	1	0.00
26	4	46	6	2025-11-14 14:13:30	2025-11-14 14:13:30	1	1	0.00
27	4	55	3	2025-11-14 14:14:22	2025-11-14 14:14:22	1	1	0.00
33	12	40	2	2025-11-14 14:20:48	2025-11-14 14:20:48	1	1	0.00
103	6	82	3	2025-11-19 08:32:10	2025-11-19 08:32:10	1	1	0.00
35	12	46	6	2025-11-14 14:22:40	2025-11-14 14:22:40	1	1	0.00
104	6	74	1	2025-11-19 08:33:13	2025-11-19 08:33:13	1	1	0.00
37	12	59	10	2025-11-14 14:50:45	2025-11-14 14:50:45	1	1	0.00
38	12	65	8	2025-11-14 14:51:35	2025-11-14 14:51:35	1	1	0.00
105	6	78	5	2025-11-19 08:35:14	2025-11-19 08:35:14	1	1	0.00
49	23	47	6	2025-11-17 07:26:32	2025-11-17 07:26:32	1	1	0.00
113	23	81	13	2025-11-19 20:12:43	2025-11-19 20:12:43	1	1	0.00
56	3	67	8	2025-11-17 07:48:27	2025-11-17 07:48:27	1	1	0.00
57	3	50	11	2025-11-17 07:49:30	2025-11-17 07:49:30	1	1	0.00
116	23	80	14	2025-11-19 20:19:28	2025-11-19 20:19:28	1	1	0.00
59	5	50	11	2025-11-17 07:56:50	2025-11-17 07:56:50	1	1	0.00
60	5	41	3	2025-11-17 07:59:14	2025-11-17 07:59:14	1	1	0.00
61	5	67	8	2025-11-17 08:00:07	2025-11-17 08:00:07	1	1	0.00
62	5	44	4	2025-11-17 08:01:06	2025-11-17 08:01:06	1	1	0.00
65	6	67	8	2025-11-17 14:42:40	2025-11-17 14:42:40	1	1	0.00
87	23	72	1	2025-11-19 07:51:00	2025-11-19 22:16:29	1	1	0.00
70	6	38	2	2025-11-17 14:48:51	2025-11-17 14:48:51	1	1	0.00
71	6	50	11	2025-11-17 14:49:56	2025-11-17 14:49:56	1	1	0.00
120	6	60	10	2025-11-20 06:08:44	2025-11-20 06:08:44	\N	1	0.00
122	23	67	11	2025-11-20 06:15:13	2025-11-20 06:15:13	\N	1	0.00
123	23	67	9	2025-11-20 06:25:53	2025-11-20 06:25:53	1	1	0.00
50	23	51	2	2025-11-17 07:27:30	2025-11-18 02:45:46	1	1	0.00
30	12	55	3	2025-11-14 14:17:44	2025-11-18 10:59:38	1	1	25000.00
3	3	39	2	2025-11-14 13:29:58	2025-11-14 13:29:58	1	1	0.00
\.


--
-- TOC entry 3696 (class 0 OID 32876)
-- Dependencies: 239
-- Data for Name: classes; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.classes (id, name, entity_id, academic_year_id, teacher_id, created_at, updated_at, school_fees, description) FROM stdin;
3	6ème	3	1	\N	2025-10-19 12:53:23	2025-10-29 08:48:14	95000.00	\N
5	5ème	3	1	\N	2025-10-29 08:48:59	2025-10-29 08:48:59	95000.00	\N
6	4ème	3	1	\N	2025-10-29 08:49:56	2025-10-29 08:49:56	105000.00	\N
7	3ème	3	1	\N	2025-10-29 08:50:56	2025-10-29 08:50:56	145000.00	\N
8	2ndeAB	3	1	\N	2025-10-29 08:51:54	2025-10-29 08:51:54	125000.00	\N
9	2ndeCD	3	1	\N	2025-10-29 08:52:38	2025-10-29 08:52:38	125000.00	\N
10	1èreCD	3	1	\N	2025-10-29 08:53:20	2025-10-29 08:53:20	125000.00	\N
11	1èreAB	3	1	\N	2025-10-29 08:54:09	2025-10-29 08:54:09	125000.00	\N
12	TleAB	3	1	\N	2025-10-29 08:55:25	2025-10-29 08:55:25	165000.00	\N
4	TleCD	3	1	\N	2025-10-19 14:40:13	2025-10-29 08:57:11	165000.00	\N
13	CI	2	1	\N	2025-10-29 08:58:43	2025-10-29 08:58:43	57000.00	\N
14	CE1	2	1	\N	2025-10-29 08:59:19	2025-10-29 08:59:19	57000.00	\N
15	CE2	2	1	\N	2025-10-29 08:59:55	2025-10-29 08:59:55	57000.00	\N
16	CM1	2	1	\N	2025-10-29 09:00:37	2025-10-29 09:00:37	57000.00	\N
17	CM2	2	1	\N	2025-10-29 09:01:08	2025-10-29 09:01:08	60000.00	\N
18	Maternelle I	1	1	\N	2025-10-29 09:01:54	2025-10-29 09:01:54	62000.00	\N
19	Maternelle II	1	1	\N	2025-10-29 09:02:36	2025-10-29 09:02:36	62000.00	\N
20	Pré-Maternelle	1	1	\N	2025-10-29 09:03:42	2025-10-29 09:03:42	70000.00	\N
2	CP	2	1	29	2025-10-19 12:32:36	2025-10-29 12:56:42	57000.00	\N
23	1ère PF	3	1	\N	2025-11-16 17:05:09	2025-11-17 19:43:00	350000.00	\N
27	3ème PF	3	1	\N	2025-11-16 17:06:26	2025-11-18 02:36:07	350000.00	\N
21	2nde PF	3	1	\N	2025-11-16 17:03:39	2025-11-18 02:37:28	350000.00	\N
\.


--
-- TOC entry 3716 (class 0 OID 41015)
-- Dependencies: 259
-- Data for Name: conducts; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.conducts (id, student_id, academic_year_id, entity_id, grade, comment, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3702 (class 0 OID 32916)
-- Dependencies: 245
-- Data for Name: enrollments; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.enrollments (id, student_id, class_id, academic_year_id, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3694 (class 0 OID 32865)
-- Dependencies: 237
-- Data for Name: entities; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.entities (id, slug, name, created_at, updated_at) FROM stdin;
1	maternelle	Maternelle	2025-10-04 22:48:37	2025-10-04 22:48:37
2	primaire	Primaire	2025-10-04 22:48:39	2025-10-04 22:48:39
3	secondaire	Secondaire	2025-10-04 22:48:40	2025-10-04 22:48:40
\.


--
-- TOC entry 3688 (class 0 OID 32834)
-- Dependencies: 231
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 3720 (class 0 OID 41073)
-- Dependencies: 263
-- Data for Name: grades; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.grades (id, student_id, subject_id, type, value, trimestre, sequence, academic_year_id, created_at, updated_at, class_id) FROM stdin;
10	131	5	interrogation	12.25	1	1	1	2025-11-26 09:46:02	2025-11-26 09:46:02	3
11	133	5	interrogation	6.00	1	1	1	2025-11-26 09:46:02	2025-11-26 09:46:02	3
12	137	5	interrogation	20.00	1	1	1	2025-11-26 09:46:03	2025-11-26 09:46:03	3
13	135	5	interrogation	17.50	1	1	1	2025-11-26 09:46:03	2025-11-26 09:46:03	3
14	134	5	interrogation	6.50	1	1	1	2025-11-26 09:46:04	2025-11-26 09:46:04	3
15	138	5	interrogation	19.00	1	1	1	2025-11-26 09:46:04	2025-11-26 09:46:04	3
16	140	5	interrogation	20.00	1	1	1	2025-11-26 09:46:05	2025-11-26 09:46:05	3
17	142	5	interrogation	16.50	1	1	1	2025-11-26 09:46:06	2025-11-26 09:46:06	3
18	144	5	interrogation	12.50	1	1	1	2025-11-26 09:46:06	2025-11-26 09:46:06	3
19	146	5	interrogation	20.00	1	1	1	2025-11-26 09:46:07	2025-11-26 09:46:07	3
20	147	5	interrogation	19.00	1	1	1	2025-11-26 09:46:07	2025-11-26 09:46:07	3
21	149	5	interrogation	7.00	1	1	1	2025-11-26 09:46:08	2025-11-26 09:46:08	3
22	151	5	interrogation	6.25	1	1	1	2025-11-26 09:46:08	2025-11-26 09:46:08	3
23	152	5	interrogation	13.50	1	1	1	2025-11-26 09:46:09	2025-11-26 09:46:09	3
24	154	5	interrogation	20.00	1	1	1	2025-11-26 09:46:09	2025-11-26 09:46:09	3
25	155	5	interrogation	17.00	1	1	1	2025-11-26 09:46:10	2025-11-26 09:46:10	3
26	157	5	interrogation	15.00	1	1	1	2025-11-26 09:46:11	2025-11-26 09:46:11	3
27	158	5	interrogation	12.00	1	1	1	2025-11-26 09:46:11	2025-11-26 09:46:11	3
28	159	5	interrogation	11.00	1	1	1	2025-11-26 09:46:12	2025-11-26 09:46:12	3
29	160	5	interrogation	19.00	1	1	1	2025-11-26 09:46:12	2025-11-26 09:46:12	3
30	162	5	interrogation	17.50	1	1	1	2025-11-26 09:46:13	2025-11-26 09:46:13	3
31	164	5	interrogation	10.50	1	1	1	2025-11-26 09:46:13	2025-11-26 09:46:13	3
32	166	5	interrogation	9.00	1	1	1	2025-11-26 09:46:14	2025-11-26 09:46:14	3
33	131	5	interrogation	6.00	1	2	1	2025-11-26 09:57:15	2025-11-26 09:57:15	3
34	133	5	interrogation	6.00	1	2	1	2025-11-26 09:57:16	2025-11-26 09:57:16	3
35	137	5	interrogation	17.00	1	2	1	2025-11-26 09:57:16	2025-11-26 09:57:16	3
36	135	5	interrogation	17.00	1	2	1	2025-11-26 09:57:17	2025-11-26 09:57:17	3
37	134	5	interrogation	9.00	1	2	1	2025-11-26 09:57:17	2025-11-26 09:57:17	3
38	138	5	interrogation	16.00	1	2	1	2025-11-26 09:57:18	2025-11-26 09:57:18	3
39	140	5	interrogation	11.00	1	2	1	2025-11-26 09:57:18	2025-11-26 09:57:18	3
40	142	5	interrogation	17.00	1	2	1	2025-11-26 09:57:19	2025-11-26 09:57:19	3
41	144	5	interrogation	17.00	1	2	1	2025-11-26 09:57:19	2025-11-26 09:57:19	3
42	146	5	interrogation	10.00	1	2	1	2025-11-26 09:57:20	2025-11-26 09:57:20	3
43	147	5	interrogation	17.00	1	2	1	2025-11-26 09:57:21	2025-11-26 09:57:21	3
44	149	5	interrogation	17.00	1	2	1	2025-11-26 09:57:21	2025-11-26 09:57:21	3
45	151	5	interrogation	8.00	1	2	1	2025-11-26 09:57:22	2025-11-26 09:57:22	3
46	152	5	interrogation	17.00	1	2	1	2025-11-26 09:57:22	2025-11-26 09:57:22	3
47	154	5	interrogation	17.00	1	2	1	2025-11-26 09:57:23	2025-11-26 09:57:23	3
48	155	5	interrogation	17.00	1	2	1	2025-11-26 09:57:23	2025-11-26 09:57:23	3
49	158	5	interrogation	1.00	1	2	1	2025-11-26 09:57:24	2025-11-26 09:57:24	3
50	159	5	interrogation	17.00	1	2	1	2025-11-26 09:57:24	2025-11-26 09:57:24	3
51	160	5	interrogation	6.00	1	2	1	2025-11-26 09:57:25	2025-11-26 09:57:25	3
52	162	5	interrogation	8.00	1	2	1	2025-11-26 09:57:25	2025-11-26 09:57:25	3
53	163	5	interrogation	6.00	1	2	1	2025-11-26 09:57:26	2025-11-26 09:57:26	3
54	164	5	interrogation	8.00	1	2	1	2025-11-26 09:57:27	2025-11-26 09:57:27	3
55	166	5	interrogation	2.00	1	2	1	2025-11-26 09:57:27	2025-11-26 09:57:27	3
56	131	5	devoir	16.75	1	1	1	2025-11-26 10:23:07	2025-11-26 10:23:07	3
57	133	5	devoir	12.75	1	1	1	2025-11-26 10:23:07	2025-11-26 10:23:07	3
58	137	5	devoir	19.25	1	1	1	2025-11-26 10:23:08	2025-11-26 10:23:08	3
59	135	5	devoir	16.50	1	1	1	2025-11-26 10:23:08	2025-11-26 10:23:08	3
60	134	5	devoir	14.00	1	1	1	2025-11-26 10:23:09	2025-11-26 10:23:09	3
61	138	5	devoir	19.00	1	1	1	2025-11-26 10:23:09	2025-11-26 10:23:09	3
62	140	5	devoir	15.75	1	1	1	2025-11-26 10:23:10	2025-11-26 10:23:10	3
63	142	5	devoir	14.50	1	1	1	2025-11-26 10:23:11	2025-11-26 10:23:11	3
64	144	5	devoir	18.00	1	1	1	2025-11-26 10:23:11	2025-11-26 10:23:11	3
65	146	5	devoir	19.00	1	1	1	2025-11-26 10:23:12	2025-11-26 10:23:12	3
66	147	5	devoir	17.50	1	1	1	2025-11-26 10:23:12	2025-11-26 10:23:12	3
67	149	5	devoir	15.00	1	1	1	2025-11-26 10:23:13	2025-11-26 10:23:13	3
68	151	5	devoir	13.25	1	1	1	2025-11-26 10:23:13	2025-11-26 10:23:13	3
69	152	5	devoir	12.50	1	1	1	2025-11-26 10:23:14	2025-11-26 10:23:14	3
70	154	5	devoir	19.00	1	1	1	2025-11-26 10:23:14	2025-11-26 10:23:14	3
71	155	5	devoir	14.25	1	1	1	2025-11-26 10:23:15	2025-11-26 10:23:15	3
72	157	5	devoir	14.75	1	1	1	2025-11-26 10:23:16	2025-11-26 10:23:16	3
73	158	5	devoir	13.75	1	1	1	2025-11-26 10:23:16	2025-11-26 10:23:16	3
74	159	5	devoir	10.25	1	1	1	2025-11-26 10:23:17	2025-11-26 10:23:17	3
75	160	5	devoir	17.25	1	1	1	2025-11-26 10:23:17	2025-11-26 10:23:17	3
76	162	5	devoir	5.00	1	1	1	2025-11-26 10:23:18	2025-11-26 10:23:18	3
77	163	5	devoir	6.75	1	1	1	2025-11-26 10:23:18	2025-11-26 10:23:18	3
78	164	5	devoir	11.75	1	1	1	2025-11-26 10:23:19	2025-11-26 10:23:19	3
79	166	5	devoir	14.75	1	1	1	2025-11-26 10:23:20	2025-11-26 10:23:20	3
80	131	5	devoir	14.75	1	2	1	2025-12-09 09:32:48	2025-12-09 09:32:48	3
81	133	5	devoir	4.00	1	2	1	2025-12-09 09:32:48	2025-12-09 09:32:48	3
82	137	5	devoir	14.75	1	2	1	2025-12-09 09:32:49	2025-12-09 09:32:49	3
83	135	5	devoir	16.00	1	2	1	2025-12-09 09:32:49	2025-12-09 09:32:49	3
84	134	5	devoir	9.50	1	2	1	2025-12-09 09:32:50	2025-12-09 09:32:50	3
85	138	5	devoir	19.50	1	2	1	2025-12-09 09:32:50	2025-12-09 09:32:50	3
86	140	5	devoir	14.25	1	2	1	2025-12-09 09:32:51	2025-12-09 09:32:51	3
87	142	5	devoir	12.25	1	2	1	2025-12-09 09:32:52	2025-12-09 09:32:52	3
88	144	5	devoir	17.50	1	2	1	2025-12-09 09:32:52	2025-12-09 09:32:52	3
89	146	5	devoir	18.50	1	2	1	2025-12-09 09:32:53	2025-12-09 09:32:53	3
90	147	5	devoir	16.50	1	2	1	2025-12-09 09:32:53	2025-12-09 09:32:53	3
91	149	5	devoir	12.00	1	2	1	2025-12-09 09:32:54	2025-12-09 09:32:54	3
92	151	5	devoir	12.75	1	2	1	2025-12-09 09:32:54	2025-12-09 09:32:54	3
93	152	5	devoir	12.50	1	2	1	2025-12-09 09:32:55	2025-12-09 09:32:55	3
94	154	5	devoir	14.00	1	2	1	2025-12-09 09:32:55	2025-12-09 09:32:55	3
95	155	5	devoir	12.75	1	2	1	2025-12-09 09:32:56	2025-12-09 09:32:56	3
96	157	5	devoir	7.25	1	2	1	2025-12-09 09:32:57	2025-12-09 09:32:57	3
97	158	5	devoir	4.00	1	2	1	2025-12-09 09:32:57	2025-12-09 09:32:57	3
98	159	5	devoir	10.25	1	2	1	2025-12-09 09:32:58	2025-12-09 09:32:58	3
99	160	5	devoir	6.00	1	2	1	2025-12-09 09:32:58	2025-12-09 09:32:58	3
100	162	5	devoir	9.25	1	2	1	2025-12-09 09:32:59	2025-12-09 09:32:59	3
101	163	5	devoir	6.00	1	2	1	2025-12-09 09:32:59	2025-12-09 09:32:59	3
102	164	5	devoir	11.25	1	2	1	2025-12-09 09:33:00	2025-12-09 09:33:00	3
103	166	5	devoir	10.50	1	2	1	2025-12-09 09:33:01	2025-12-09 09:33:01	3
\.


--
-- TOC entry 3704 (class 0 OID 32940)
-- Dependencies: 247
-- Data for Name: invitations; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.invitations (id, email, invited_by, role_id, academic_year_id, token, used_at, created_at, updated_at, entity) FROM stdin;
\.


--
-- TOC entry 3686 (class 0 OID 32826)
-- Dependencies: 229
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- TOC entry 3685 (class 0 OID 32817)
-- Dependencies: 228
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- TOC entry 3677 (class 0 OID 32769)
-- Dependencies: 220
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_09_10_221839_create_roles_tables	1
5	2025_09_10_222330_academic_years	1
6	2025_09_10_222401_entities	1
7	2025_09_10_222426_classes	1
8	2025_09_10_222449_subjects	1
9	2025_09_10_222553_students	1
10	2025_09_10_222631_enrollments	1
11	2025_09_10_222657_invitations	1
12	2025_09_11_001335_add_role_id_to_users_table	1
13	2025_09_11_091826_add_entity_to_invitations_and_classes_table	1
14	2025_09_11_092153_remove_entity_from_classes_table	1
15	2025_09_11_123225_add_school_fees_to_classes_table	1
16	2025_09_11_161456_add_profile_photo_to_users_table	1
17	2025_09_13_201003_create_teacher_invitations_table	2
18	2025_09_13_203709_create_class_teacher_subject_table	2
19	2025_09_13_204942_create_timetables_table	2
20	2025_09_15_142309_add_gender_to_students_table	2
21	2025_09_15_190256_add_parent_phone_and_birth_place_to_students_table	2
22	2025_09_17_224828_add_details_to_teachers_table	2
23	2025_09_18_065600_add_documents_to_users_table	2
24	2025_09_18_152703_add_accepted_at_to_teacher_invitations_table	2
25	2025_09_20_105457_create_student_payments_table	2
26	2025_09_20_141104_add_validation_to_students_table	2
27	2025_09_20_200450_add_validation_to_students_table	2
28	2025_09_21_005036_make_school_fees_nullable_in_students_table	3
29	2025_09_21_031846_add_description_to_classes_table	3
30	2025_09_21_201112_add_academic_year_to_tables	3
31	2025_09_23_005213_add_academic_year_id_to_students_table	3
32	2025_09_23_095345_add_academic_year_id_at_to_subjets_table	3
33	2025_09_25_082010_create_punishments_table	3
34	2025_09_25_082039_create_conducts_table	3
35	2025_09_25_095442_add_hours_to_punishments_table	3
36	2025_09_25_200203_add_classe_id_to_teacher_invitations_table	3
37	2025_09_26_064830_add_classe_id_to_subjects_table	3
38	2025_09_26_130523_create_schedules_table	3
39	2025_09_26_180210_create_grades_table	3
40	2025_09_26_180311_create_subject_averages_table	3
41	2025_09_26_180417_create_student_trimestre_averages_table	3
42	2025_09_26_180457_create_student_annual_averages_table	3
43	2025_09_26_181920_add_coefficient_to_subjects_table	3
44	2025_09_28_114130_create_note_permissions_table	3
45	2025_09_11_171657_create_students_table	4
46	2025_10_04_231224_add_birth_date_to_students_table	5
47	2025_10_04_231937_add_entity_id_to_students_table	6
48	2025_10_04_233135_add_nofind_to_students_table	7
49	2025_10_04_233510_add_age_to_students_table	8
50	2025_10_04_233510_add_ageto_students_table	9
51	2025_11_01_000310_create_cahier_de_texte_table	10
52	2025_11_01_080000_add_class_id_to_grades_table	11
53	2025_11_13_062429_create_note_edit_permissions_table	12
54	2025_11_18_033609_add_missing_columns_to_cahier_de_texte_table	13
55	2025_11_18_050152_column_to_cahier_de_texte	14
56	2025_11_18_071106_add_amount_brut_to_class_teacher_subject_table	15
57	2025_11_25_063649_add_open_close_dates_to_note_permissions	16
58	2025_11_25_070934_rename_open_columns_in_note_permissions	17
59	2025_11_25_072148_rename_open_columns_in_note_permissions2	18
\.


--
-- TOC entry 3732 (class 0 OID 409605)
-- Dependencies: 275
-- Data for Name: note_edit_permissions; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.note_edit_permissions (id, teacher_id, class_id, subject_id, academic_year_id, trimestre, type, is_active, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3728 (class 0 OID 41153)
-- Dependencies: 271
-- Data for Name: note_permissions; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.note_permissions (id, class_id, trimestre, is_open, created_at, updated_at, open_at, close_at) FROM stdin;
5	3	2	f	2025-10-29 10:07:52	2025-10-29 10:07:52	\N	\N
6	3	3	f	2025-10-29 10:07:54	2025-10-29 10:07:54	\N	\N
8	10	2	f	2025-11-01 07:33:37	2025-11-01 07:33:37	\N	\N
9	10	3	f	2025-11-01 07:33:38	2025-11-01 07:33:38	\N	\N
10	5	1	f	2025-11-12 12:33:48	2025-11-12 12:33:48	\N	\N
11	5	2	f	2025-11-12 12:33:49	2025-11-12 12:33:49	\N	\N
14	6	2	f	2025-11-12 12:34:31	2025-11-12 12:34:31	\N	\N
15	6	3	f	2025-11-12 12:34:32	2025-11-12 12:34:32	\N	\N
12	5	3	t	2025-11-12 12:33:49	2025-11-24 09:28:19	\N	\N
13	6	1	t	2025-11-12 12:34:31	2025-11-24 09:37:25	\N	\N
17	7	2	f	2025-11-24 09:37:58	2025-11-24 09:37:58	\N	\N
18	7	3	f	2025-11-24 09:37:59	2025-11-24 09:37:59	\N	\N
16	7	1	t	2025-11-24 09:37:58	2025-11-24 09:39:09	\N	\N
20	9	2	f	2025-11-24 09:39:54	2025-11-24 09:39:54	\N	\N
21	9	3	f	2025-11-24 09:39:54	2025-11-24 09:39:54	\N	\N
19	9	1	t	2025-11-24 09:39:53	2025-11-24 09:40:01	\N	\N
7	10	1	t	2025-11-01 07:33:36	2025-11-24 09:40:34	\N	\N
23	12	2	f	2025-11-24 09:41:11	2025-11-24 09:41:11	\N	\N
24	12	3	f	2025-11-24 09:41:12	2025-11-24 09:41:12	\N	\N
22	12	1	t	2025-11-24 09:41:10	2025-11-24 09:41:19	\N	\N
26	4	2	f	2025-11-24 09:41:43	2025-11-24 09:41:43	\N	\N
27	4	3	f	2025-11-24 09:41:44	2025-11-24 09:41:44	\N	\N
25	4	1	t	2025-11-24 09:41:42	2025-11-24 09:43:38	\N	\N
29	23	2	f	2025-11-24 09:43:59	2025-11-24 09:43:59	\N	\N
30	23	3	f	2025-11-24 09:43:59	2025-11-24 09:43:59	\N	\N
32	27	2	f	2025-11-24 09:44:28	2025-11-24 09:44:28	\N	\N
33	27	3	f	2025-11-24 09:44:29	2025-11-24 09:44:29	\N	\N
31	27	1	t	2025-11-24 09:44:28	2025-11-24 09:44:36	\N	\N
35	21	2	f	2025-11-24 09:44:58	2025-11-24 09:44:58	\N	\N
36	21	3	f	2025-11-24 09:44:59	2025-11-24 09:44:59	\N	\N
34	21	1	t	2025-11-24 09:44:57	2025-11-24 09:45:05	\N	\N
4	3	1	t	2025-10-29 10:07:50	2025-11-25 07:29:50	2025-11-25 08:00:00	2025-11-25 08:32:00
28	23	1	f	2025-11-24 09:43:58	2025-11-25 16:51:50	\N	\N
\.


--
-- TOC entry 3680 (class 0 OID 32786)
-- Dependencies: 223
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
campuscineclub35@gmail.com	$2y$12$MRy0UIBu.7nKQqn8HgihzOMZLBz/3F5.XEjAqB/ANLw6UrcuoSoRi	2025-10-11 08:37:21
secretaire@gmail.com	$2y$12$7SfSZDnBZAnEsXGdmqXAkeQHYuYOE9nCqX8lrdrX9nhLmqppB0v.q	2025-11-07 20:08:38
aguemonconstant1@gmail.com	$2y$12$0kbwYJKxebCsB8aUTwr03eIvO8qagPKo/Nt9xeFC7rxSlRkkVZrrW	2025-11-10 19:21:29
\.


--
-- TOC entry 3714 (class 0 OID 40991)
-- Dependencies: 257
-- Data for Name: punishments; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.punishments (id, student_id, academic_year_id, entity_id, reason, date_punishment, created_at, updated_at, hours) FROM stdin;
\.


--
-- TOC entry 3690 (class 0 OID 32846)
-- Dependencies: 233
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.roles (id, name, display_name, created_at, updated_at) FROM stdin;
3	directeur_primaire	Directeur Primaire	2025-10-04 22:47:56	2025-10-04 22:47:56
4	censeur	Directeur Primaire	2025-10-04 22:47:59	2025-10-04 22:47:59
5	surveillant	Directeur Primaire	2025-10-04 22:48:02	2025-10-04 22:48:02
6	secretaire	Directeur Primaire	2025-10-04 22:48:05	2025-10-04 22:48:05
7	super_admin	Super Administrateur	2025-10-04 22:48:32	2025-10-04 22:48:32
8	enseignant	Enseignant	2025-10-04 22:48:36	2025-10-04 22:48:36
\.


--
-- TOC entry 3718 (class 0 OID 41051)
-- Dependencies: 261
-- Data for Name: schedules; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.schedules (id, classe_id, teacher_id, subject_id, day_of_week, start_time, end_time, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3681 (class 0 OID 32793)
-- Dependencies: 224
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
sZYxd9dCrUaODyOFJUAnXpM6mS24CYRM0xpaUrcJ	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoic3c0TXNiTDlzWThPa05BNThXZ0huekJPcjZWTDJVaTN5aExiN0RLOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766294512
5zVg9zE8hpUeRplawVJiORUyHjWqWRGeziRwmuMm	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiQUNaREtySzNVdTg1aWVLSGRvTEtubGk1bVJYOHQxUDdNMUhENk5xayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766294817
efdEpXS75cgYS0gyG19H3jg6P2I7EPPRAq1jlZmm	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoicTRQYXVjVlJ3QnRqZG5GQkpqVGQ5aFA2aWVPQ1J6QVVwWEtIMlRwUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766295124
CgJM79BCYpGbFbciMOZXJaQCzuwUzVNYsDJaRrMm	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNmJyS0xvRnlzMjF5eUUzZW9JcjAwc2lCbVdKcDJRTDJkdVJRT29wRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766295432
oNH9AiQxg09aZ7IBcIWBsznY87q5a60hpTecJsCt	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidHhmMHkzS1BuR1BHRmsxeU5vZThFTVRwZGhnZ0JibWlJYldnSVF3SyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766295738
51P5Wayk26am23reem5WoC3CdkwKiyuitY53Cfzu	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNUVyeDJwVk5kOFJpUm5uN0w2M3M3dW1vYlpUdUthTFRSUzZYQzJadSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766296045
k3dcGRMRlSc2hOapdWeSKgmFn1mav8E4yZRb66Cx	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoieEtSYUNPbURNMkw4bUVLcHR4azZ2MWVTUnBXcDVueDN5a05OVzJ1diI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766296350
9J0SKlLoUStjBRvOwrYGV9bLTTcVV9EXd5syavAC	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoib25RZG9UZlp5dTEwRjRSRXdTbEJXWXcwM2tyMVFEcTd0RkFObG9VTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766296656
NvN84KSvuY8Kbd51G9j46CWXscWI7ULr0atKAI8g	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiVk1rYnpyT3dtbEdsSlFHb2dSbnZXVE5wQUJkc3BzR0c3TEoyMFluRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766296963
OpSB7tThTfRYWt9ckDKNmNR3QXfVqnHMH4HZZUOO	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidElUQlhMNGJwY1BYa25lUkxROWxWZG5sRDdrMURXSkxpTWxRd29MViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766323024
dxoy1avITBg1CvyKa3GehJr0ZatAfgiN3siK27Hs	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiTTRsQlhodTFuZWRjUmNIaDNkcmZQS1d6d2l1VGRKdjVTSDhOTWJrayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766323331
eznmNbxenWYHB8i4lmSOdf0CNnJRahrN98PiuG3n	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidG5NNk9VeWpRaUQ3Y09FNFVraldnUnd0SWFMSE50Vnp5eDdMVGhBaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766323638
fLXOr4q0hSXQTePn9nXyFtiV7gOGtqhiQai3fuVA	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiS3hTU1ZIM0JZeXJIYkU4T214Qm4zbHBlVFgyMEpQNGxHVHpRNWpjWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766323944
xj0xf3EJEZW2Bh68kd5D9kdtGEjlGSnttuyjbpn7	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRVZRajF3UzRLRkNJV2tYaU5zWFpWS01pU09vdU44NjQ3UEJZUkZaOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766324250
Wlb3lCAqyAlieeDmtVVazS33n9KCvJVrJpSTSlJ7	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiN1dqSGdZZFIzVEI0SXp0NlY4WUxtUHlDMEVOaWlkdlR3alRGU3JCSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766324557
fRpQ88BUf7svO8ZZeDUayjTeTQcUtM4iW6FF0Wsa	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoid0hRSE5INE1FN0xJUUV4RDA5UVpvczRibDIzZE5wZkVqZlFHMjNLSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766324863
3ow3Q10cwWKpWv5t8yosbQ0bbDp7tZPEIihAhiQm	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaEx1cTFVU0pHUDQ5a28zdHl6dkxPM0hkT0c0VGZqaml0N3FnMU9EbSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766325170
JPC1GAsxUu3t2eFODQ92TiOnwyo71Po8RR2iyoHf	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiMzVGeHJGZ2t2cHRxTU8ycll0YTI5WjFwallFbjNldlY1eGI3QW5SdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766325477
SZeIn35eSvxK72k26oSbxUNPSpr1IMfvzxMBq7Lk	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNlRJSlpLOHh1eGVlRFdDUzR1ekFETDB1MEd3N2xrN09VdTA1Q3lhSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766325783
s8qUuraVDuRlcZlW9iCxLb1jbtS8RxyUyjaBphCQ	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRkVNYnJqek15SWhyN2FyQ0dUSVJ5S3NxTjZWcHVWejg1NThVVjBxRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766326089
GhDgRpET9pByZ65Z9WirNdIlwjNpwVrlgsepmCK8	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYkp4MWtCYXFzVWtGWE13emp2ekY1SHJmMFdHbTludEhaRklGR1doNSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766326397
0mCUez0FDKReO5vsHriZgNC7dra4bVgxN28Gj4cv	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaG9KMVE4WFpjNUt4YllPMXVSeVZJRDk4aUpha1ZLRVZrZ2JHdThUayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766297270
mnbUcZ7rpaCFNyryF5EwRjkOWmjburzfYRo4Bgky	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiajlXcEt5dmYwMTN1VnBPVFVsYWQ0eXhQWUc5R0J2VjRuNUR6eFJFayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766297575
EfRQPp8fryT2t4B2HYFN9gMdvfjUXYQewzshGd2t	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWEY2emZRWTJrcXNXNUtCeUEwR3VSdHYya1ljU0Y5V2xBTkgwS2lxbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766297881
NDMq6TOWggrxXAXhsvikerAuQNHqFT2khucC7ymE	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaDYyOUFDRE5HT2hrcDZIak1IZFV3bzcwRTlCOEt0akxiZEZWOXNWSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766298189
eMYq6hMwdvXVycPrOqeFQQYxqW5tQ0R4ZIfw0Nzc	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiQ0o5a0lmY0g2ak5RZFJsTmJXdTNsOUhmSTRzOXZtTkZHeFFGWlJlYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766298495
3U5YSOHbxrG8fLyhWCr9QXmMLWkc8QzWYTjoyWBh	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNnhNT0owZDNBZ1dwMnJZb05FbkNNWFVRQlZsVm1qN3VHV0JkQ2l4MCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766298802
VyRZf2brwhPt0Flgk5tsPvnTVmmLrly3XI6WcNwH	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaHkwdEtXUllkRWpZaFB2MEJ5SVdoOFdNQ1VKU3VkVkY0NWtxckt5ViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766299109
sXBmnr5ke5ICD2fx2SCYllduLL9f3g547cy11Nc4	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaWwwdHRBUzJrd2dzOU5GYXJVY3lteHBkMW5DdmNUWml2VEgxbzJmZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766299415
QEdvdbO95HdDcoQmky5uUfmriuhNHkyTJ1WV8GXR	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaHNIeTYwbGNGbVdHa1Z4TlBMTkxpNEdiT2FsaFFqRjc5Tzc1Q05tRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766299722
1KO9KtSbVisAJMZOjXwMfOJB7cEold1mPFg2oWUM	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiSGpwdnd6RHZwQ2ZQQmkyRmVIVVRhNFdxZTlHOEZkS05vVjB3a1RXYyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766300029
Jwl3bxHHkAnzEsvvS4v3Zj3ot18jAg5kfWZFLJyM	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoicTRoWDlOS3J4MnlJMGQ1dWswM3RwSUozYnFaZEUwZlYwQ0dYbEdWUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766300335
tPUJtXlvFkRb0FkbH0tx7L3GOmn8ltLVtHPcMeZG	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiZnpLbmZvb0FiaTNsMk1iS0Q4WGRDNGJpWDFod0g0UTY2dlgyREdObSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766300642
vhv4xjv7WmXZwGUmiXOWXOi2Ab9gCpCqLjd7oIRi	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaWRNeVJZa0x5RUxPY2Z1RzJNVVdKVnFoVnl6ZHJMcmZtQ2lYSWJmaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766300948
qZgFX6K5v7i97JrkZpVm5uzJWc4C1iy4I1i0FExe	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYThJUUJVN0h5MXB1alRyRzhZQk1BcVlZN3dpcGNHT3VsbEo4QVNwNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766301256
qzAkqLtRzgB1nCwOjD9Qcob0O45w8lxD3IiIBkQG	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRmRPeTVWTXlzd3JNOFhBSGQ4MmRTN2RodU1jc084cUZNWWRjOEMwWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766301562
GofuqN34rFaAOCbi5dy1xj7nTSL1jaw5gQ7vui2n	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYlg0V3FvSlJxV1dCUUlFeDhaMWM4dnRGUUdERUMyUlZqOUxzUWVzTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766301869
W9DwFnkjCpKO1paOKgpwTrd0BuOnONk4GwwTepRF	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiOWRibkM4blpaQXV1YllsQkdxSHNqT0ZScnlYaFhsQ2NWSjVhTzMxcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766302175
RgWhdqQK1YUrMJBUpf8opL337RXRWPyntdpRz1f6	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiS1dUbWhBV1dQSWlmWVA4YUxnRzZDdVViY3h1b2ZSTzFORmpaR0dIWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766302482
r5YEuJaz68Pxph70ywhM8fgQrr3nFUfumGFN7A1a	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiVDc1RWRlUmFOMUtEclNrQlJDU3FHMVR5MkpNWldRWU9YSmpsVHRsSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766302789
DUewWSe65i2hqAwmari9LihCGI3zeik0uAjmZrOx	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiQUtIemdzRzVJV0NqWUFUN1RJY1pqaFZ2ODRtYVRFZlFKUW5NWThwbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766303097
5j80ZEUnVvqKdSb2MIrfJ62QiExr85UYV6ChkZtc	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaG9YRWw5d0RjcUJ3NVFxRWs3TEk3NGhnVVZEbjlkUjZ3YmFWS3hBOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766303403
t0NRf4XvmIz45uzmJgWA3Rry0L3BEQOYVPPCLwG7	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiUEZzRmRIYmZwd1JGQ0pyZm04amo5dGtQdXJRaWxjTk5sb002WERMOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766303711
0SfOtgBP2sEOprFuck12MxMB6WwqJTESYwHfmiNS	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaG5WdklleEZna2hxZGdralpWTVFscDV6c016eHJOUjlodGxxSTNFbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766304017
k9BvaqLFzmvK0lKnCZW4mCWrZ8UPzY4uf53H3ClB	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoid0VjZk1tck54TEthanViRUh1VmxyM2R2bEdMdUJUS3p2U2NUNFJqVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766304323
NhboJfd98aW7M9RoFEsGU5MWsVJOWO7Vtcg9gNNU	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWHhHRjQ2TzgyaUsxQUpKdW8wbFVUWnZZVEpIaFJYVVlKTVRiZ2lLNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766304629
aRT19oJCRbI7iswzEc4kx6d2NE9UHHNS8Bemf89H	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYUxXZG5keVpuMlhMNjdka1phdXQ2OHk0Y0RZdUlYZmV1bEhqRFFIaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766304935
KEsDv7ol9kZD5Dv4fUCJhLBEP7tCtBoBknqD6Lb8	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYkc2ZWd3emU4NFJUNUVReUlwYkJqM0VkZ1hCV0haQ1BGSmhvdHFCZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766305242
p8r7WwlwaG5A33cvEcEEDbx2vtpoZE6U4xgHy6XU	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibVlsTHhRbmxPNDJOZkxwSXhwa1dWdllUTjc0MlF2TEFybXRYM25HWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766305549
keN2xXUr7puJqLQuArQDdRJcMFWrrhvnRDiXtlvq	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiSGlEVkJOSnpISTJkYUFkaW5zNndNb09mZE41cGlHVlpBZ0Z6STNrOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766305855
X9FdJmOy5ihjcS6Imh8BqUIBOAjY7pJLqYkNcfZY	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoia0pQNERYT2txVXBPU0N3QXBCdERiSnlReVBaM0N0MWU4cVR1N3ZGSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766306160
V0u94MFHfw47HitSAuUodOZo78Z6xnEP8FZDheWm	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibWRMTHlIVm5IWWJmMkNtUno2ZlNDYWltQm12ajhqald0WGRUbFJYUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766306466
l8Sn3MymmhuoMtdOPSc1zyumV8TufyR4QFgyj8Od	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiVmRrNW8xdWRFT2RjOUd0c2hseDZyekZxa3V4ajRDZ2dzT3p4eWY4cSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766306773
ShlTX4TTbIfjEojOY2axO1DKEIBpYvnrIAajot1c	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNlg0VGthR1UwTk9OYUlzbURqU2h1SktuM0lEOHlzbGZXeHJqc3k4aCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766307080
UfnzhYiIVByTaXPhBMWUPNMmTFHQKUN025qimc4i	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRW55RzJaaTNqT1F4UVFVY1ltUDc4bER6SmFRMm0wVnpNemowcW9NbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766307386
k3f4Hk5R7ItdKrexYQwdH1JUMKpFcgF9eyIAoN3u	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiN1dSSmVMbVBsVDNIZ0NCNjlCR2ZsU09tSU9ZZ09YSXRPOHpBWWx0SyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766307693
WldS3RfjzayWM7JataVH8Vgb1AeALvduv9pmZXrG	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoianM2ajN4SGFaYVlpNUtpV1lqUVlPOTFmVTZDdlF5amV5VDFlcXZ5UCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766307999
WOrDcbbw2zq7SKBXGhkuvfCev024QkqwOKlocZbq	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiN3pxdnVySFR5QVRjenRmUXZ1dlRCZFhTTUx3NUtFb1VkTzVrbGJhYyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766308306
gebYeKqqX0A58fIahuszxEcw4NqcpqffIqhoOhTm	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiakNTVG1TRXQ5YTVmVnBWVkZJTkh3WUZhaTNZblFYT2k0U3JYalpwayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766308613
5qi1AHfExIAQcio0eLM1FHHzRxSBRVPnE7jrB6SR	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRzJsZUwzcGVWeDJteHhTcENET0tKcUlNVFQ0WXBIWWhIZDd0bmY5UCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766308919
7kdUCGUEp6OqjrIoDA02xtwzVXvMOsvPF94Ta7sd	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiTVJTbkd6NEZiUnlhMGFlS3U2eWJzVHI3TUFFZjI0MURNaUpjYnNaRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766309226
k0luFeMMQPxi4bNCTyU5HBYWo9YdaALPFHjh2iGe	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidkJqSmJQMDRVQ2hRSzN4U1VsbUxRU0lyM3RIODZub0dhWjI0V1R2NiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766309532
IlNUHbP0bz1JLsnthHnF40gF3ZsnS0lZ6Yc0gYvz	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiVjRmblBjY0VYcENJSWVFZm9scDdlVUxtanVPT1RTbXJVVWlUU3VCZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766309838
wpKQagApKwhacbfqeWvMpugFckomiIl6OvKggPkQ	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibjZSSTh1Tmk0TnB6ZW1hcE03SWZmUVJYMVVJYndEckJPd2d0OXZqWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766310145
z4f6fheQssO8hAVjdHhiAXO9sbDqUD7cDC1PrJK4	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNWJtanc2N1pjQnVFdlJMZ0ZrclE3NjJETzdIVXVmM3kwajNISHhrMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766310451
WmocBsdp7wQV06DJ1xtKtCVdI2yo3SklTFPGcxFe	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoid2hKU3hUVTVyODQ5dU5oU00xY2pyWjNRMEhkWTdOaVkxYTdRejFPRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766310758
w7eILyLehK815ix1Y979KRaSIu4ExfDQKkk8i4C5	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRFBHWmJ1aXhjSVlFeDVTajBHYjdhRzRGeXlObjJnZ09Tb2NhbWFwOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766311064
oGY7DnGUqSalsz9Zf75ymhhjKJYALIEcDCNhckNk	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiY242NGVHM2JhYTFKSDlvTVJSekV0MGhRenB3WEQ5a1hhSnhpQzloaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766311371
3heYdN42v2dIzf25w1zV5Dr4VKW9sPVXJ59GpcbC	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoienB0QmozV2c3bDNmclVrSGdlbEtlRGdVMGxFZEVTVXZMV2FLQUkwNSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766287151
uyaX2wtiYfLVk0OJ2UOSeqREESY7CwdtmvUREqzG	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiMlRzSkdYd3BGd2lvT0NWaHRVeUo5QWNTYkNoVHp4ajJyNEJ0RmE1aiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766287458
QHarn0w2fn0rJHEa6Awqfc7borwgWqzRc5WMtgs8	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoickt5dkpjMW9oWmF3czN0QU5tZWdzNjlVZktLMmZpNnpmWUh5VHBJRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766287763
go25374IXrzzRZ9LahNbfUDgAEBEQaaNvJiadkhY	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNzJrWWNXU0g0QmR5MDZRTkNqMnppWmdHTU1VTVhQV0xIM01MeVZJTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766288070
swZwQryLwFwql5qWE3CTCQZjrIs6KX17YVvjLvUu	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiSnpGN0V3c29hQnY2a3A3RWdBTmhQaDV4RHY4TTJqeTB1aHZQOVRVaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766288378
TXDrXn9dxOxMQ5RhLM8ixbZH1xZkEXBEoEuOi4fT	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiZGk1T1E5a1BMMHo0Q1NHYmhTcTY3Q280TFlrYWRLSzlvTzZGbGEwcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766288685
gi0zmNY1xuw5cQZp26e8DLuwpxb6nChyxeyjgcwP	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidE1DeE9iWkhEdnlyVzdNSE10cjdsWkxpbWY0WEdXM2I1MExZVURMQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766288992
rhhqhyOfYZO6Yo0IQCT6PKiGZe4LDFukO4FW4dKD	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiY0tVcGE1NTdIVXhtdUhqdWdlcXEwTG1raHpEcmdDRUJFazAwOHBWUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766289299
lQghAswSNRSyL1PLEOpO20LhhFXwu9EgyVGUZNMF	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWDJ1UjhHWEw1dmx3SFpWWFRSYUZhTmRJQnhHamY4VnBYRVFDM2l2biI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766289606
Q7C9SlIf4pWCUvuTZcoK7rB1Zdilk5m2yKe0NVJX	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiNVFCYVJGYXJpR0d2eEhLOFYxNnNtM1J5V0VqMWNRckVLdDJJNzhGdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766311679
N52zz1dvidE68jKo8FqY9TF2YqrJUlGTRnshccMH	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiS29lNndRTG0wekNhTkVPbFdWRm9xVVpqaXhNeUNybm12NGhSenR2NyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766289912
yGcTtjDiiGmWCKIQyoOP7jdsp0UjrBkJ1kGiHBec	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYTN4UGZYNUZwcndCMmJuQ3g2MEE3UGtDeUpyYWxtS0lNTHhJQXdmUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766290219
FlVfXSmnWTaDXBC9JHuJHWhbxZx3FHPsQaCQGntw	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiZnVJYmtPaGJSUEY2SXRsT2FNNko0OExyWFo4UlhPYWxTQlJKRk16UyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766311985
t7dv3DaMBR4urSB7j6xbqx6xDwZbwxkuNxYyyhIz	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYUhVUG9iT1o5TTBJQXRWWXRSblpiY1VqYlg2Wmg5cnhDZXdMYWQ3SCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766312292
LgeW02VKHLLLlKPmcWIBKRDjlFv54VzMbClV5SN8	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoienN3bE1NcXltRnhNaVlTdFcyaFNJM1pveXB3NjRvOEZhUkpzWWRwdCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766312599
WQCSwNjlKD6O8UL0DENXaTIbhN0BpAk1Uodi06fz	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoia3I0MkdId1pLRWlQZlVtNklnaGFiSWhudXByN1lFRUZid3hvSEhSaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766312906
ZcHPwCCz74ZoJyrBqMeeVHbEQgVqRtqo99yRg5Vs	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiMkFYZTZHMW9XaFhUVnpuWG9UTUxkbklyaXRuT0x6ckRQUXJmM1JXaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766313212
AdoRVB1UoElhl00gqZxAowTTdD2UoXv4Lae5ZXkR	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaDJhakdERG5NSVkyOEZxNjNkTUtBZTI5blhFaHRqcTJEQ29nRlBJQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766313519
0lf8QjRLRgjVoJJ4JRWppQLuQPRNnhvFkazB6Qd9	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiTTc2TURUZXdkbWZPSDlWbVoxeGFkaWtlSThUbVVicG9NUVlSSEljaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766313825
O6jbPNOAETY9kj2PhmpEgcxO1u4S97NtuHIkMz62	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYWIyQTJhb0JhMmd2MW95MnhjUlc3WVRrSzBVS1hyU3pNekxEcWlHMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766314132
S9I76Yvz7KgndhWUTzUYCymVKJV0zlk10FJxtcMr	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiUzhKckJYNTdhR3FyTTRMQ2dHZVVidjQxd215VEpmUnpIcE9nRDNlZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766314438
fKVs7kL3GG3epoEZ9ZuzVxwOgUWnS6HJ31pKZCZ9	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiajdxMVY4QlNpaWpEOEVHWjB3Y2w4clk2TDdsUmtjNXViUkVGRFp4NiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766314745
8g1OcsysD8PKf4B13gcehUIZQtFh134iTAn8Y4Dc	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiS2JXUHJCbTBIcEFzMGFBZUVzdHlEZXhGUHl0SDhNOHp2OWhOMFdwVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766315052
Ij7yFK9FB25f8vGLV9IQXtwf2sNKTg4DJXfziPig	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiOFZTOFQ3QXlQRllPYVhYTGpSeGJCODEySlZxaGhmdU1hQXdiTWM5ayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766315357
vB6PAAMsQ7NhoCOIMlhafOh2ZPM71WbH8UX4wORY	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiRHV4SjJsOHRYcEtscHMyZU1QUlEwcWxSQ1VDWllKR1FHR09xOWdodyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766290525
c6Fk8Upc0SWWUGeO9vUYPhLzB7r18pVwyymZrL8B	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWUNTV3czU1p4SlA3VW1rYlNTZDM5YmJxR2UxZzNUQ0p5T0hNMG9jbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766290833
t7j9dQlXXPGeVHvbbsxrkenljtCkIys3DrPb7SjX	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibUU3OXJjUkZYOUNwZjIzWXRZRDM5R0NXRk9DMGhSWEdnbU55TG1HNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766291141
8UM8B9Iz7qNTJrdS6pqYHfCRRxXnWnzu7FCHRlro	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiZFZyNVBSZTZWMnVhYVR3aTlSWVBUaXpVYTlkZTZjazBuMUs3bXVaQyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766291446
mDhFXQbrUQ7Ft7IJaovpf8Eey1kuyAjBWYMiqQRi	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiUlpYTjNpZjQxSmRRZjRBd2xWRmpOVGp5MVJKazVrSXNkWmxHQXBURSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766291753
8CeteWR5NuRZ3KAMuhVR7pROQpyXUUznhTQwVoI6	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiM0ZjYUdTdVMzTEpjME5BZ3BuajJHZU1lRjI4cEFwTVpCcG9pRnVGVCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766292059
K8P2fhoeiJJsyH9sBEl2Fc1ii5md9vwlL5pJ9aHb	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiOTFLbkZYbGQ4eUFnRExXMlg4M2gyNkZ2NThWdndjTTdtRVpJUGw1bSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766292365
NIczKNb0ynZFSFMB4k3gAXs2xuEuNmHeuz1dHfBZ	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWm1hTDlEcXFwa0ozTXRlbGc0RDV6WGdtQWd2enZGbFZCMkduekxyRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766292672
bj7S4Hl9wbCASEDfe4m3DYoG1N6G4nNHR2ycqIwS	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoieloyYVFwQ0pvVDRseExBdnBUNnBIQWllZUZ6Nk53ckZpQmhlaW5WdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766292978
eCi838TH5Kni3t2XFZsc0dM8WCdRvZEmUuW4wtG0	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiN21CVHEwc2x1dDJsU2ZVa3dtYUZoUkt5Wm5MQW1FMjE1OXlPOFZzbSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766293285
UnXgpTJhdiZeuguOJB1NyJ1Fa1vDIITHhEW7iqbS	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiZ2x3TTZ4b0N6VFNnOUhNeE1scW9RcGdqQ2tOaUdBTUl1ODhuYmFnSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766315664
FWf1DhUO7UuDGUyguF9yIhZJ1VbvREVNLuANN6hZ	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiVUtOdTJsQVNwWXZ2WXc2SlJSM0xna2k0azVhZ0dFYTY1MVBHcmRxciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766315971
FXDcXFdJl3rk1ImnWReeyZpy2B46L0NiKRN4RRBK	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidjZhV1RMUUdUa3hPS281dVNXR1Q2MkVDV3FZSzhzT3BOQ3Vac0xZMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766316278
HCqUFioxmhyquZAOWCYLV3eJkKQWt090zTjB8Vgx	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiczdVb1A3djViMXZDS1ZVV0huM3dlWHZDbmU5R1pPSVhFaDJjMGx2aCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766316585
ZJPphZrUW2cjjb2dUUF8La7oaa9EEMAEXHZ9Jd0A	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiT0Y3dFJab0tWMllnd256V2c5blR4b0poMTFvYzRtejVGMUFwQlM2MiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766316891
jjrQFKwpV2SdeiEVoG8fxGP6OKVqGXDKFCodC0Eb	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiREFJcjVHeFljZ1pGTWFtYUZmbDJQdjBFU0gwWUxXdGNOSzUyZ21QaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766317198
dYZNThsQf7BF65FqLIDkNjaX9w5ScBfRLKURcxFG	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWXB1V1RJQkdSR2Fid3MyNGpFMFZvUjJQV09xQzZTUklGSDlqV01uciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766317506
vzihkTT5cGUiDXA2dOPqzmeMTxRGHO02lcJAU7qS	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiT2h0a1c4cVE1ME5IelJobmpuT0VVZHo4bHRwM2U4OWE1QldGNDJjZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766317813
1sayu0C0CAr2KUyvKjHytsco2FWZ4HKMHMzuKEL9	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoicThMNWVJTW0xVWJncFRORDBobGlydHVaOEpTQ0tiNEJ5d0E0SHA5ZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766318121
5avCq6JQuYJ0bhHELpvqQBEVrmzP5YMz2qqp5BC6	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoidkdocmpTeUNRc1dKR0J2OVI2ODVmNUN5U3NSdVQyOGJnbVdyaGtWeSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766318427
tESLHEMvJ253iS3iOLWoAMYrrDgRwi2wdVvYnw0C	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiQVQ3UFBDcFZYMk5YSXowVGRFdnlabTBVZjZBWFJqbHJQemJFb1ppMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766318734
QplRTwiC3ISNzc7prJOkDPkp4ci5l30Yqbheqx4U	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiOGFxTzF4ZnRVYXNRd3VXOWFxTTVxR0VJalk5V2p3ZHVFd0EycjVYbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766319040
ziOwHf0Nsp0tJUsSM0nhRtx7eZvl7LdR1NYsfSd8	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoib2g4MW1uNFM5Q1Y1Y0JITXp4TnAwMGxabkdmMFVRNUZONFU4eERUdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766293592
8CE77URvw4uBBEizFS44QEtYRYFYPKJ8RE1paS0q	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiSmgyUVRCWEZMMlE5RnBtNFBGVks3bEdsbXNDdno4RDVOSGdLR0pIeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766293898
M10D4p8dUhvTEav56uOpbPXXzEJiMrMqWRgvDLbv	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiWkJSU0s3Nkh0bDU0ckFRc1ppUFBoME1aMG43c1hya3AzZm1kT2N5MiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766294205
eqaLwzw14zGpm7tr5M0S0pXS76aeUS56gFRVXBTc	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiY3JhY1puZ1p2Q0UyVTBzdFF0d2VXdW55bkJpWTJBdnQ2bXFIbEd3biI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766319348
56bms8US6y4a65bEWIK9nk9ivWCrdFpvVjEqxQdj	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibjlveWdQOGZVTWpQOVFtd1JMMVBORUxhQkxwRFdyZVJkeEhnQWpnYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766319655
ndnj3vGgA7pTIaHDVFGGE4NFS3yv1eMYaodbkVSu	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiMmt0SGFRSzNma2xyWjVBSmdkNXI2UG9VYWl3dkduT0dGeEY3TzNqOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766319961
CQ8WDEpiTT2WebGKPM8wQQGvzWbb1ba19KYADTfF	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiQU1RdGZhNmhHYVNkVE45TjEza2dKS2JJb2xqbkVCZDBtdFNRbFlrMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766320267
NKzfPWvQweFMexfHRWobZO3RgzJQcv4EqN6RlM74	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYm9JRkRGRVIySlNUanF4TkVkbXVEaVp1NTRabUJXVHEwUWpSWEUwVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766320575
kbrTg9vY423U7F9zQUzg0jFAJwjdT3bVUqTn2cPb	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiaExIVmh2TkFSRjVxd3RIU2xRSU9BazgxenhsS0JOSTNOTVJNVmhldCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766320881
giU5m81wldjygp0li4jM12JXhmwWFn4n4rr8mQ1Q	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiYlBFR01Md3lSZWxrdWdQcDdWVEhXdWNzckhzWW9VN2RHVnlPMnVmWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766321188
KpfyI6kCggYlE9y0cwdcGBFPcqckVlijtOUszQwW	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoicGZTcnBFdUpLUDVmNzJmUDVWTmsxNTY4Tm5aY0ljWG5GWGhHeVpMWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766321492
n3kknBeT3yHCN9pl2qlJVf7oFoBiqzia8mX0oEzX	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibzdaNm1LZVNzS1F4VGxzSU1RODNId0xKNG90TWlEUzBCcGNCRlBaVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766321799
1UkkR300xLixl7UdujR4SJgpRdGsYd1l3u4vOe4e	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoibVo0WkQ0MlBJa2QxUTdZR2w5ckxkdk5KblFCUTRrVkNZdFF5RDR4WiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766322104
3e1Im7GklJr6TrzoxUFMBDsc87KazyY2WOfjJPVR	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiQmM0SDhzY0ZxQlh2cjNVelJsenN3WGYyS0pXZkJLTGJvS3RXZlE1NyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766322410
Ic1UaAP1P6WfLrhLseUH707yB1f95oOMqxWPVzTh	\N	::1	Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)	YToyOntzOjY6Il90b2tlbiI7czo0MDoiS0lWM3IyVExEdzBBMHpmRlVSNnR2dWFIYlE2N05mQU9IUk5OTk8xYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1766322717
\.


--
-- TOC entry 3726 (class 0 OID 41135)
-- Dependencies: 269
-- Data for Name: student_annual_averages; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.student_annual_averages (id, student_id, average, rank, academic_year_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3712 (class 0 OID 33043)
-- Dependencies: 255
-- Data for Name: student_payments; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.student_payments (id, student_id, tranche, amount, payment_date, receipt, created_at, updated_at, academic_year_id) FROM stdin;
81	62	1	0.00	2025-10-29	\N	2025-10-29 16:38:46	2025-10-29 16:38:46	\N
82	63	1	0.00	2025-10-29	\N	2025-10-29 16:42:21	2025-10-29 16:42:21	\N
83	64	1	0.00	2025-10-29	\N	2025-10-29 16:43:55	2025-10-29 16:43:55	\N
84	65	1	0.00	2025-10-29	\N	2025-10-29 16:45:47	2025-10-29 16:45:47	\N
85	66	1	0.00	2025-10-29	\N	2025-10-29 16:48:18	2025-10-29 16:48:18	\N
33	14	1	0.00	2025-10-29	\N	2025-10-29 13:25:48	2025-10-29 13:25:48	\N
34	15	1	0.00	2025-10-29	\N	2025-10-29 13:28:51	2025-10-29 13:28:51	\N
35	16	1	0.00	2025-10-29	\N	2025-10-29 13:31:01	2025-10-29 13:31:01	\N
36	17	1	0.00	2025-10-29	\N	2025-10-29 13:33:24	2025-10-29 13:33:24	\N
37	18	1	0.00	2025-10-29	\N	2025-10-29 13:36:27	2025-10-29 13:36:27	\N
38	19	1	0.00	2025-10-29	\N	2025-10-29 13:38:56	2025-10-29 13:38:56	\N
39	20	1	0.00	2025-10-29	\N	2025-10-29 13:41:25	2025-10-29 13:41:25	\N
40	21	1	0.00	2025-10-29	\N	2025-10-29 13:43:51	2025-10-29 13:43:51	\N
41	22	1	0.00	2025-10-29	\N	2025-10-29 13:46:14	2025-10-29 13:46:14	\N
42	23	1	0.00	2025-10-29	\N	2025-10-29 13:49:00	2025-10-29 13:49:00	\N
43	24	1	0.00	2025-10-29	\N	2025-10-29 13:51:46	2025-10-29 13:51:46	\N
44	25	1	0.00	2025-10-29	\N	2025-10-29 13:54:58	2025-10-29 13:54:58	\N
45	26	1	0.00	2025-10-29	\N	2025-10-29 13:58:01	2025-10-29 13:58:01	\N
46	27	1	0.00	2025-10-29	\N	2025-10-29 14:00:25	2025-10-29 14:00:25	\N
47	28	1	0.00	2025-10-29	\N	2025-10-29 14:04:41	2025-10-29 14:04:41	\N
48	29	1	0.00	2025-10-29	\N	2025-10-29 14:08:08	2025-10-29 14:08:08	\N
49	30	1	0.00	2025-10-29	\N	2025-10-29 14:12:32	2025-10-29 14:12:32	\N
50	31	1	0.00	2025-10-29	\N	2025-10-29 14:16:11	2025-10-29 14:16:11	\N
51	32	1	0.00	2025-10-29	\N	2025-10-29 14:18:15	2025-10-29 14:18:15	\N
52	33	1	0.00	2025-10-29	\N	2025-10-29 14:20:58	2025-10-29 14:20:58	\N
53	34	1	0.00	2025-10-29	\N	2025-10-29 14:23:38	2025-10-29 14:23:38	\N
54	35	1	0.00	2025-10-29	\N	2025-10-29 14:25:27	2025-10-29 14:25:27	\N
55	36	1	0.00	2025-10-29	\N	2025-10-29 14:27:25	2025-10-29 14:27:25	\N
56	37	1	0.00	2025-10-29	\N	2025-10-29 14:29:33	2025-10-29 14:29:33	\N
57	38	1	0.00	2025-10-29	\N	2025-10-29 14:42:40	2025-10-29 14:42:40	\N
58	39	1	0.00	2025-10-29	\N	2025-10-29 14:44:14	2025-10-29 14:44:14	\N
59	40	1	0.00	2025-10-29	\N	2025-10-29 14:46:20	2025-10-29 14:46:20	\N
60	41	1	0.00	2025-10-29	\N	2025-10-29 14:48:55	2025-10-29 14:48:55	\N
61	42	1	0.00	2025-10-29	\N	2025-10-29 14:52:17	2025-10-29 14:52:17	\N
62	43	1	0.00	2025-10-29	\N	2025-10-29 14:54:15	2025-10-29 14:54:15	\N
63	44	1	0.00	2025-10-29	\N	2025-10-29 14:56:29	2025-10-29 14:56:29	\N
64	45	1	0.00	2025-10-29	\N	2025-10-29 14:59:04	2025-10-29 14:59:04	\N
65	46	1	0.00	2025-10-29	\N	2025-10-29 15:01:45	2025-10-29 15:01:45	\N
66	47	1	0.00	2025-10-29	\N	2025-10-29 15:03:27	2025-10-29 15:03:27	\N
67	48	1	0.00	2025-10-29	\N	2025-10-29 15:05:57	2025-10-29 15:05:57	\N
68	49	1	0.00	2025-10-29	\N	2025-10-29 15:09:17	2025-10-29 15:09:17	\N
69	50	1	0.00	2025-10-29	\N	2025-10-29 15:14:37	2025-10-29 15:14:37	\N
70	51	1	0.00	2025-10-29	\N	2025-10-29 15:18:44	2025-10-29 15:18:44	\N
71	52	1	0.00	2025-10-29	\N	2025-10-29 15:22:06	2025-10-29 15:22:06	\N
72	53	1	0.00	2025-10-29	\N	2025-10-29 15:24:18	2025-10-29 15:24:18	\N
73	54	1	0.00	2025-10-29	\N	2025-10-29 15:26:47	2025-10-29 15:26:47	\N
74	12	1	0.00	2025-10-29	\N	2025-10-29 15:30:10	2025-10-29 15:30:10	\N
75	55	1	0.00	2025-10-29	\N	2025-10-29 15:43:47	2025-10-29 15:43:47	\N
76	56	1	0.00	2025-10-29	\N	2025-10-29 15:47:06	2025-10-29 15:47:06	\N
77	57	1	0.00	2025-10-29	\N	2025-10-29 15:49:53	2025-10-29 15:49:53	\N
78	59	1	0.00	2025-10-29	\N	2025-10-29 16:06:59	2025-10-29 16:06:59	\N
79	60	1	0.00	2025-10-29	\N	2025-10-29 16:09:34	2025-10-29 16:09:34	\N
80	61	1	0.00	2025-10-29	\N	2025-10-29 16:17:24	2025-10-29 16:17:24	\N
86	67	1	0.00	2025-10-29	\N	2025-10-29 16:50:14	2025-10-29 16:50:14	\N
87	68	1	0.00	2025-10-29	\N	2025-10-29 16:52:18	2025-10-29 16:52:18	\N
88	69	1	0.00	2025-10-29	\N	2025-10-29 16:54:22	2025-10-29 16:54:22	\N
89	70	1	0.00	2025-10-29	\N	2025-10-29 16:57:12	2025-10-29 16:57:12	\N
90	71	1	0.00	2025-10-29	\N	2025-10-29 17:02:09	2025-10-29 17:02:09	\N
91	58	1	0.00	2025-10-29	\N	2025-10-29 17:05:25	2025-10-29 17:05:25	\N
92	72	1	0.00	2025-10-30	\N	2025-10-30 18:37:44	2025-10-30 18:37:44	\N
93	73	1	0.00	2025-10-30	\N	2025-10-30 18:40:15	2025-10-30 18:40:15	\N
94	74	1	0.00	2025-10-31	\N	2025-10-31 02:45:31	2025-10-31 02:45:31	\N
95	75	1	0.00	2025-10-31	\N	2025-10-31 19:26:02	2025-10-31 19:26:02	\N
96	76	1	0.00	2025-10-31	\N	2025-10-31 19:28:39	2025-10-31 19:28:39	\N
97	77	1	0.00	2025-10-31	\N	2025-10-31 19:32:01	2025-10-31 19:32:01	\N
98	78	1	0.00	2025-10-31	\N	2025-10-31 19:37:37	2025-10-31 19:37:37	\N
99	79	1	0.00	2025-10-31	\N	2025-10-31 19:40:07	2025-10-31 19:40:07	\N
100	80	1	0.00	2025-10-31	\N	2025-10-31 19:44:00	2025-10-31 19:44:00	\N
101	81	1	0.00	2025-10-31	\N	2025-10-31 19:50:49	2025-10-31 19:50:49	\N
102	82	1	0.00	2025-10-31	\N	2025-10-31 19:53:04	2025-10-31 19:53:04	\N
103	83	1	0.00	2025-10-31	\N	2025-10-31 20:04:35	2025-10-31 20:04:35	\N
104	84	1	0.00	2025-10-31	\N	2025-10-31 20:08:22	2025-10-31 20:08:22	\N
106	86	1	0.00	2025-11-01	\N	2025-11-01 19:56:57	2025-11-01 19:56:57	\N
107	87	1	0.00	2025-11-01	\N	2025-11-01 19:59:41	2025-11-01 19:59:41	\N
108	88	1	0.00	2025-11-01	\N	2025-11-01 20:02:05	2025-11-01 20:02:05	\N
109	89	1	0.00	2025-11-01	\N	2025-11-01 20:04:16	2025-11-01 20:04:16	\N
110	90	1	0.00	2025-11-01	\N	2025-11-01 20:05:51	2025-11-01 20:05:51	\N
111	91	1	0.00	2025-11-01	\N	2025-11-01 20:07:47	2025-11-01 20:07:47	\N
112	92	1	0.00	2025-11-01	\N	2025-11-01 20:10:33	2025-11-01 20:10:33	\N
113	93	1	0.00	2025-11-01	\N	2025-11-01 20:14:54	2025-11-01 20:14:54	\N
114	94	1	0.00	2025-11-01	\N	2025-11-01 20:16:43	2025-11-01 20:16:43	\N
115	95	1	0.00	2025-11-01	\N	2025-11-01 20:18:31	2025-11-01 20:18:31	\N
116	96	1	0.00	2025-11-01	\N	2025-11-01 20:21:36	2025-11-01 20:21:36	\N
117	97	1	0.00	2025-11-01	\N	2025-11-01 20:25:25	2025-11-01 20:25:25	\N
118	98	1	0.00	2025-11-01	\N	2025-11-01 20:31:32	2025-11-01 20:31:32	\N
119	99	1	0.00	2025-11-01	\N	2025-11-01 20:35:56	2025-11-01 20:35:56	\N
120	100	1	0.00	2025-11-01	\N	2025-11-01 20:37:43	2025-11-01 20:37:43	\N
121	101	1	0.00	2025-11-01	\N	2025-11-01 20:39:50	2025-11-01 20:39:50	\N
122	102	1	0.00	2025-11-01	\N	2025-11-01 20:43:55	2025-11-01 20:43:55	\N
123	103	1	0.00	2025-11-01	\N	2025-11-01 20:45:41	2025-11-01 20:45:41	\N
124	104	1	0.00	2025-11-01	\N	2025-11-01 20:54:20	2025-11-01 20:54:20	\N
125	105	1	0.00	2025-11-01	\N	2025-11-01 20:56:15	2025-11-01 20:56:15	\N
126	106	1	0.00	2025-11-01	\N	2025-11-01 20:58:15	2025-11-01 20:58:15	\N
127	107	1	0.00	2025-11-01	\N	2025-11-01 21:00:11	2025-11-01 21:00:11	\N
128	108	1	0.00	2025-11-01	\N	2025-11-01 21:02:10	2025-11-01 21:02:10	\N
129	109	1	0.00	2025-11-01	\N	2025-11-01 21:03:40	2025-11-01 21:03:40	\N
130	110	1	0.00	2025-11-01	\N	2025-11-01 21:04:52	2025-11-01 21:04:52	\N
131	111	1	0.00	2025-11-01	\N	2025-11-01 21:07:03	2025-11-01 21:07:03	\N
132	112	1	0.00	2025-11-01	\N	2025-11-01 21:08:26	2025-11-01 21:08:26	\N
133	113	1	0.00	2025-11-01	\N	2025-11-01 21:11:50	2025-11-01 21:11:50	\N
134	114	1	0.00	2025-11-02	\N	2025-11-02 02:02:27	2025-11-02 02:02:27	\N
135	115	1	0.00	2025-11-02	\N	2025-11-02 02:06:06	2025-11-02 02:06:06	\N
136	116	1	0.00	2025-11-02	\N	2025-11-02 02:09:23	2025-11-02 02:09:23	\N
137	117	1	0.00	2025-11-02	\N	2025-11-02 02:12:50	2025-11-02 02:12:50	\N
138	118	1	0.00	2025-11-02	\N	2025-11-02 02:15:30	2025-11-02 02:15:30	\N
139	119	1	0.00	2025-11-02	\N	2025-11-02 02:17:42	2025-11-02 02:17:42	\N
140	120	1	0.00	2025-11-02	\N	2025-11-02 02:20:30	2025-11-02 02:20:30	\N
141	121	1	0.00	2025-11-02	\N	2025-11-02 02:23:43	2025-11-02 02:23:43	\N
142	122	1	0.00	2025-11-02	\N	2025-11-02 02:26:25	2025-11-02 02:26:25	\N
143	123	1	0.00	2025-11-02	\N	2025-11-02 02:28:23	2025-11-02 02:28:23	\N
144	124	1	0.00	2025-11-02	\N	2025-11-02 02:31:07	2025-11-02 02:31:07	\N
145	125	1	0.00	2025-11-02	\N	2025-11-02 02:34:52	2025-11-02 02:34:52	\N
146	126	1	0.00	2025-11-02	\N	2025-11-02 07:42:51	2025-11-02 07:42:51	\N
147	127	1	0.00	2025-11-02	\N	2025-11-02 07:45:25	2025-11-02 07:45:25	\N
148	128	1	0.00	2025-11-02	\N	2025-11-02 07:47:38	2025-11-02 07:47:38	\N
149	129	1	0.00	2025-11-02	\N	2025-11-02 07:50:29	2025-11-02 07:50:29	\N
150	130	1	0.00	2025-11-02	\N	2025-11-02 08:26:02	2025-11-02 08:26:02	\N
151	131	1	0.00	2025-11-02	\N	2025-11-02 08:30:37	2025-11-02 08:30:37	\N
152	132	1	0.00	2025-11-02	\N	2025-11-02 08:33:16	2025-11-02 08:33:16	\N
153	133	1	0.00	2025-11-02	\N	2025-11-02 08:33:32	2025-11-02 08:33:32	\N
154	134	1	0.00	2025-11-02	\N	2025-11-02 08:36:04	2025-11-02 08:36:04	\N
155	135	1	0.00	2025-11-02	\N	2025-11-02 08:39:35	2025-11-02 08:39:35	\N
156	136	1	0.00	2025-11-02	\N	2025-11-02 08:43:07	2025-11-02 08:43:07	\N
157	137	1	0.00	2025-11-02	\N	2025-11-02 08:43:52	2025-11-02 08:43:52	\N
158	138	1	0.00	2025-11-02	\N	2025-11-02 08:45:49	2025-11-02 08:45:49	\N
159	139	1	0.00	2025-11-02	\N	2025-11-02 08:46:26	2025-11-02 08:46:26	\N
160	140	1	0.00	2025-11-02	\N	2025-11-02 08:48:13	2025-11-02 08:48:13	\N
161	141	1	0.00	2025-11-02	\N	2025-11-02 08:53:29	2025-11-02 08:53:29	\N
162	142	1	0.00	2025-11-02	\N	2025-11-02 08:53:46	2025-11-02 08:53:46	\N
163	143	1	0.00	2025-11-02	\N	2025-11-02 08:57:04	2025-11-02 08:57:04	\N
164	144	1	0.00	2025-11-02	\N	2025-11-02 08:58:58	2025-11-02 08:58:58	\N
165	145	1	0.00	2025-11-02	\N	2025-11-02 09:00:20	2025-11-02 09:00:20	\N
166	146	1	0.00	2025-11-02	\N	2025-11-02 09:00:51	2025-11-02 09:00:51	\N
167	147	1	0.00	2025-11-02	\N	2025-11-02 09:02:18	2025-11-02 09:02:18	\N
168	148	1	0.00	2025-11-02	\N	2025-11-02 09:03:03	2025-11-02 09:03:03	\N
169	149	1	0.00	2025-11-02	\N	2025-11-02 09:04:06	2025-11-02 09:04:06	\N
170	150	1	0.00	2025-11-02	\N	2025-11-02 09:05:19	2025-11-02 09:05:19	\N
171	151	1	0.00	2025-11-02	\N	2025-11-02 09:05:34	2025-11-02 09:05:34	\N
172	152	1	0.00	2025-11-02	\N	2025-11-02 09:06:57	2025-11-02 09:06:57	\N
173	153	1	0.00	2025-11-02	\N	2025-11-02 09:08:05	2025-11-02 09:08:05	\N
174	154	1	0.00	2025-11-02	\N	2025-11-02 09:08:31	2025-11-02 09:08:31	\N
175	155	1	0.00	2025-11-02	\N	2025-11-02 09:10:14	2025-11-02 09:10:14	\N
176	156	1	0.00	2025-11-02	\N	2025-11-02 09:10:49	2025-11-02 09:10:49	\N
177	157	1	0.00	2025-11-02	\N	2025-11-02 09:11:49	2025-11-02 09:11:49	\N
178	158	1	0.00	2025-11-02	\N	2025-11-02 09:14:56	2025-11-02 09:14:56	\N
179	159	1	0.00	2025-11-02	\N	2025-11-02 09:16:31	2025-11-02 09:16:31	\N
180	160	1	0.00	2025-11-02	\N	2025-11-02 09:19:03	2025-11-02 09:19:03	\N
181	161	1	0.00	2025-11-02	\N	2025-11-02 09:20:19	2025-11-02 09:20:19	\N
182	162	1	0.00	2025-11-02	\N	2025-11-02 09:24:10	2025-11-02 09:24:10	\N
183	163	1	0.00	2025-11-02	\N	2025-11-02 09:26:05	2025-11-02 09:26:05	\N
184	164	1	0.00	2025-11-02	\N	2025-11-02 09:27:27	2025-11-02 09:27:27	\N
185	165	1	0.00	2025-11-02	\N	2025-11-02 09:27:40	2025-11-02 09:27:40	\N
186	166	1	0.00	2025-11-02	\N	2025-11-02 09:29:18	2025-11-02 09:29:18	\N
187	167	1	0.00	2025-11-02	\N	2025-11-02 09:30:18	2025-11-02 09:30:18	\N
188	168	1	0.00	2025-11-02	\N	2025-11-02 09:32:12	2025-11-02 09:32:12	\N
189	169	1	0.00	2025-11-02	\N	2025-11-02 09:33:37	2025-11-02 09:33:37	\N
190	170	1	0.00	2025-11-02	\N	2025-11-02 09:33:42	2025-11-02 09:33:42	\N
191	171	1	0.00	2025-11-02	\N	2025-11-02 09:35:31	2025-11-02 09:35:31	\N
192	172	1	0.00	2025-11-02	\N	2025-11-02 09:36:18	2025-11-02 09:36:18	\N
193	173	1	0.00	2025-11-02	\N	2025-11-02 09:36:51	2025-11-02 09:36:51	\N
194	174	1	0.00	2025-11-02	\N	2025-11-02 09:37:22	2025-11-02 09:37:22	\N
195	175	1	0.00	2025-11-02	\N	2025-11-02 09:38:55	2025-11-02 09:38:55	\N
196	176	1	0.00	2025-11-02	\N	2025-11-02 09:39:04	2025-11-02 09:39:04	\N
197	177	1	0.00	2025-11-02	\N	2025-11-02 09:39:56	2025-11-02 09:39:56	\N
198	178	1	0.00	2025-11-02	\N	2025-11-02 09:40:10	2025-11-02 09:40:10	\N
199	179	1	0.00	2025-11-02	\N	2025-11-02 09:41:15	2025-11-02 09:41:15	\N
200	180	1	0.00	2025-11-02	\N	2025-11-02 09:42:35	2025-11-02 09:42:35	\N
201	181	1	0.00	2025-11-02	\N	2025-11-02 09:43:04	2025-11-02 09:43:04	\N
202	182	1	0.00	2025-11-02	\N	2025-11-02 09:43:18	2025-11-02 09:43:18	\N
203	183	1	0.00	2025-11-02	\N	2025-11-02 09:45:25	2025-11-02 09:45:25	\N
204	184	1	0.00	2025-11-02	\N	2025-11-02 09:47:38	2025-11-02 09:47:38	\N
205	185	1	0.00	2025-11-02	\N	2025-11-02 09:49:14	2025-11-02 09:49:14	\N
206	186	1	0.00	2025-11-02	\N	2025-11-02 09:49:59	2025-11-02 09:49:59	\N
207	187	1	0.00	2025-11-02	\N	2025-11-02 09:52:02	2025-11-02 09:52:02	\N
208	188	1	0.00	2025-11-02	\N	2025-11-02 09:52:15	2025-11-02 09:52:15	\N
209	189	1	0.00	2025-11-02	\N	2025-11-02 09:55:34	2025-11-02 09:55:34	\N
210	190	1	0.00	2025-11-02	\N	2025-11-02 09:56:40	2025-11-02 09:56:40	\N
211	191	1	0.00	2025-11-02	\N	2025-11-02 09:58:54	2025-11-02 09:58:54	\N
212	192	1	0.00	2025-11-02	\N	2025-11-02 09:59:48	2025-11-02 09:59:48	\N
213	193	1	0.00	2025-11-02	\N	2025-11-02 10:01:14	2025-11-02 10:01:14	\N
214	194	1	0.00	2025-11-02	\N	2025-11-02 10:02:30	2025-11-02 10:02:30	\N
215	195	1	0.00	2025-11-02	\N	2025-11-02 10:02:47	2025-11-02 10:02:47	\N
216	196	1	0.00	2025-11-02	\N	2025-11-02 10:04:08	2025-11-02 10:04:08	\N
217	197	1	0.00	2025-11-02	\N	2025-11-02 10:05:23	2025-11-02 10:05:23	\N
218	198	1	0.00	2025-11-02	\N	2025-11-02 10:05:23	2025-11-02 10:05:23	\N
219	199	1	0.00	2025-11-02	\N	2025-11-02 10:07:00	2025-11-02 10:07:00	\N
220	200	1	0.00	2025-11-02	\N	2025-11-02 10:07:16	2025-11-02 10:07:16	\N
221	201	1	0.00	2025-11-02	\N	2025-11-02 10:09:33	2025-11-02 10:09:33	\N
222	202	1	0.00	2025-11-02	\N	2025-11-02 10:10:36	2025-11-02 10:10:36	\N
223	203	1	0.00	2025-11-02	\N	2025-11-02 10:12:14	2025-11-02 10:12:14	\N
224	204	1	0.00	2025-11-02	\N	2025-11-02 10:13:06	2025-11-02 10:13:06	\N
225	205	1	0.00	2025-11-02	\N	2025-11-02 10:14:55	2025-11-02 10:14:55	\N
226	206	1	0.00	2025-11-02	\N	2025-11-02 10:16:00	2025-11-02 10:16:00	\N
227	207	1	0.00	2025-11-02	\N	2025-11-02 10:18:00	2025-11-02 10:18:00	\N
228	208	1	0.00	2025-11-02	\N	2025-11-02 10:18:43	2025-11-02 10:18:43	\N
229	209	1	0.00	2025-11-02	\N	2025-11-02 10:20:23	2025-11-02 10:20:23	\N
230	210	1	0.00	2025-11-02	\N	2025-11-02 10:21:40	2025-11-02 10:21:40	\N
231	211	1	0.00	2025-11-02	\N	2025-11-02 10:23:53	2025-11-02 10:23:53	\N
232	212	1	0.00	2025-11-02	\N	2025-11-02 10:25:26	2025-11-02 10:25:26	\N
233	213	1	0.00	2025-11-02	\N	2025-11-02 10:27:21	2025-11-02 10:27:21	\N
234	214	1	0.00	2025-11-02	\N	2025-11-02 10:28:54	2025-11-02 10:28:54	\N
235	215	1	0.00	2025-11-02	\N	2025-11-02 10:30:27	2025-11-02 10:30:27	\N
236	216	1	0.00	2025-11-02	\N	2025-11-02 10:31:53	2025-11-02 10:31:53	\N
237	217	1	0.00	2025-11-02	\N	2025-11-02 10:32:07	2025-11-02 10:32:07	\N
238	218	1	0.00	2025-11-02	\N	2025-11-02 10:33:45	2025-11-02 10:33:45	\N
239	219	1	0.00	2025-11-02	\N	2025-11-02 10:34:14	2025-11-02 10:34:14	\N
240	220	1	0.00	2025-11-02	\N	2025-11-02 10:35:34	2025-11-02 10:35:34	\N
241	221	1	0.00	2025-11-02	\N	2025-11-02 10:36:12	2025-11-02 10:36:12	\N
242	222	1	0.00	2025-11-02	\N	2025-11-02 10:37:40	2025-11-02 10:37:40	\N
243	223	1	0.00	2025-11-02	\N	2025-11-02 10:37:54	2025-11-02 10:37:54	\N
244	224	1	0.00	2025-11-02	\N	2025-11-02 10:39:49	2025-11-02 10:39:49	\N
245	225	1	0.00	2025-11-02	\N	2025-11-02 10:39:51	2025-11-02 10:39:51	\N
246	226	1	0.00	2025-11-02	\N	2025-11-02 10:41:32	2025-11-02 10:41:32	\N
247	227	1	0.00	2025-11-02	\N	2025-11-02 10:42:25	2025-11-02 10:42:25	\N
248	228	1	0.00	2025-11-02	\N	2025-11-02 10:43:57	2025-11-02 10:43:57	\N
249	229	1	0.00	2025-11-02	\N	2025-11-02 10:45:59	2025-11-02 10:45:59	\N
250	230	1	0.00	2025-11-02	\N	2025-11-02 10:46:04	2025-11-02 10:46:04	\N
251	231	1	0.00	2025-11-02	\N	2025-11-02 10:48:57	2025-11-02 10:48:57	\N
252	232	1	0.00	2025-11-02	\N	2025-11-02 10:50:37	2025-11-02 10:50:37	\N
253	233	1	0.00	2025-11-02	\N	2025-11-02 10:52:07	2025-11-02 10:52:07	\N
254	234	1	0.00	2025-11-02	\N	2025-11-02 10:52:17	2025-11-02 10:52:17	\N
255	235	1	0.00	2025-11-02	\N	2025-11-02 10:54:07	2025-11-02 10:54:07	\N
256	236	1	0.00	2025-11-02	\N	2025-11-02 10:54:52	2025-11-02 10:54:52	\N
257	237	1	0.00	2025-11-02	\N	2025-11-02 10:56:13	2025-11-02 10:56:13	\N
258	238	1	0.00	2025-11-02	\N	2025-11-02 10:56:45	2025-11-02 10:56:45	\N
259	239	1	0.00	2025-11-02	\N	2025-11-02 10:59:00	2025-11-02 10:59:00	\N
260	240	1	0.00	2025-11-02	\N	2025-11-02 11:00:10	2025-11-02 11:00:10	\N
261	241	1	0.00	2025-11-02	\N	2025-11-02 11:01:39	2025-11-02 11:01:39	\N
262	242	1	0.00	2025-11-02	\N	2025-11-02 11:03:53	2025-11-02 11:03:53	\N
263	243	1	0.00	2025-11-02	\N	2025-11-02 11:04:08	2025-11-02 11:04:08	\N
264	244	1	0.00	2025-11-02	\N	2025-11-02 11:05:48	2025-11-02 11:05:48	\N
265	245	1	0.00	2025-11-02	\N	2025-11-02 11:06:18	2025-11-02 11:06:18	\N
266	246	1	0.00	2025-11-02	\N	2025-11-02 11:08:03	2025-11-02 11:08:03	\N
267	247	1	0.00	2025-11-02	\N	2025-11-02 11:08:32	2025-11-02 11:08:32	\N
268	248	1	0.00	2025-11-02	\N	2025-11-02 11:10:05	2025-11-02 11:10:05	\N
269	249	1	0.00	2025-11-02	\N	2025-11-02 11:23:50	2025-11-02 11:23:50	\N
270	250	1	0.00	2025-11-02	\N	2025-11-02 11:25:04	2025-11-02 11:25:04	\N
271	251	1	0.00	2025-11-02	\N	2025-11-02 11:27:09	2025-11-02 11:27:09	\N
272	252	1	0.00	2025-11-02	\N	2025-11-02 11:34:37	2025-11-02 11:34:37	\N
273	253	1	0.00	2025-11-02	\N	2025-11-02 11:38:20	2025-11-02 11:38:20	\N
274	254	1	0.00	2025-11-02	\N	2025-11-02 11:41:29	2025-11-02 11:41:29	\N
275	255	1	0.00	2025-11-02	\N	2025-11-02 11:47:14	2025-11-02 11:47:14	\N
276	256	1	0.00	2025-11-02	\N	2025-11-02 11:49:22	2025-11-02 11:49:22	\N
277	257	1	0.00	2025-11-02	\N	2025-11-02 11:51:34	2025-11-02 11:51:34	\N
278	258	1	0.00	2025-11-02	\N	2025-11-02 11:55:10	2025-11-02 11:55:10	\N
279	259	1	0.00	2025-11-02	\N	2025-11-02 11:57:35	2025-11-02 11:57:35	\N
280	260	1	0.00	2025-11-02	\N	2025-11-02 11:59:28	2025-11-02 11:59:28	\N
281	261	1	0.00	2025-11-02	\N	2025-11-02 12:03:13	2025-11-02 12:03:13	\N
282	262	1	0.00	2025-11-02	\N	2025-11-02 12:07:46	2025-11-02 12:07:46	\N
283	263	1	0.00	2025-11-02	\N	2025-11-02 12:10:24	2025-11-02 12:10:24	\N
284	264	1	0.00	2025-11-02	\N	2025-11-02 12:13:05	2025-11-02 12:13:05	\N
285	265	1	0.00	2025-11-02	\N	2025-11-02 12:15:17	2025-11-02 12:15:17	\N
286	266	1	0.00	2025-11-02	\N	2025-11-02 12:18:13	2025-11-02 12:18:13	\N
287	267	1	0.00	2025-11-02	\N	2025-11-02 12:20:19	2025-11-02 12:20:19	\N
288	268	1	0.00	2025-11-02	\N	2025-11-02 12:22:57	2025-11-02 12:22:57	\N
289	269	1	0.00	2025-11-02	\N	2025-11-02 12:25:06	2025-11-02 12:25:06	\N
290	270	1	0.00	2025-11-02	\N	2025-11-02 12:26:49	2025-11-02 12:26:49	\N
291	271	1	0.00	2025-11-02	\N	2025-11-02 12:28:38	2025-11-02 12:28:38	\N
292	272	1	0.00	2025-11-02	\N	2025-11-02 12:31:01	2025-11-02 12:31:01	\N
293	273	1	0.00	2025-11-02	\N	2025-11-02 12:38:38	2025-11-02 12:38:38	\N
294	274	1	0.00	2025-11-02	\N	2025-11-02 12:40:38	2025-11-02 12:40:38	\N
295	275	1	0.00	2025-11-02	\N	2025-11-02 12:42:31	2025-11-02 12:42:31	\N
296	276	1	0.00	2025-11-02	\N	2025-11-02 12:50:35	2025-11-02 12:50:35	\N
297	277	1	0.00	2025-11-02	\N	2025-11-02 12:59:03	2025-11-02 12:59:03	\N
298	278	1	0.00	2025-11-02	\N	2025-11-02 13:05:12	2025-11-02 13:05:12	\N
299	279	1	0.00	2025-11-02	\N	2025-11-02 17:20:51	2025-11-02 17:20:51	\N
300	280	1	0.00	2025-11-02	\N	2025-11-02 17:23:11	2025-11-02 17:23:11	\N
301	281	1	0.00	2025-11-02	\N	2025-11-02 17:28:11	2025-11-02 17:28:11	\N
302	282	1	0.00	2025-11-02	\N	2025-11-02 17:30:24	2025-11-02 17:30:24	\N
303	283	1	0.00	2025-11-02	\N	2025-11-02 17:32:36	2025-11-02 17:32:36	\N
304	284	1	0.00	2025-11-02	\N	2025-11-02 17:40:31	2025-11-02 17:40:31	\N
305	285	1	0.00	2025-11-02	\N	2025-11-02 17:44:09	2025-11-02 17:44:09	\N
306	286	1	0.00	2025-11-02	\N	2025-11-02 17:54:23	2025-11-02 17:54:23	\N
307	287	1	0.00	2025-11-02	\N	2025-11-02 17:56:20	2025-11-02 17:56:20	\N
308	288	1	0.00	2025-11-02	\N	2025-11-02 17:58:33	2025-11-02 17:58:33	\N
309	289	1	0.00	2025-11-02	\N	2025-11-02 18:01:51	2025-11-02 18:01:51	\N
310	290	1	0.00	2025-11-02	\N	2025-11-02 18:03:43	2025-11-02 18:03:43	\N
311	291	1	0.00	2025-11-02	\N	2025-11-02 18:05:41	2025-11-02 18:05:41	\N
312	292	1	0.00	2025-11-02	\N	2025-11-02 18:08:00	2025-11-02 18:08:00	\N
313	293	1	0.00	2025-11-02	\N	2025-11-02 18:20:44	2025-11-02 18:20:44	\N
314	294	1	0.00	2025-11-02	\N	2025-11-02 18:22:50	2025-11-02 18:22:50	\N
315	295	1	0.00	2025-11-02	\N	2025-11-02 18:24:16	2025-11-02 18:24:16	\N
316	296	1	0.00	2025-11-02	\N	2025-11-02 18:26:01	2025-11-02 18:26:01	\N
317	297	1	0.00	2025-11-18	\N	2025-11-18 15:52:29	2025-11-18 15:52:29	\N
318	298	1	0.00	2025-11-18	\N	2025-11-18 16:02:42	2025-11-18 16:02:42	\N
319	299	1	0.00	2025-11-18	\N	2025-11-18 16:05:39	2025-11-18 16:05:39	\N
320	300	1	0.00	2025-11-18	\N	2025-11-18 16:06:03	2025-11-18 16:06:03	\N
321	301	1	0.00	2025-11-18	\N	2025-11-18 16:06:47	2025-11-18 16:06:47	\N
322	302	1	0.00	2025-11-18	\N	2025-11-18 16:07:01	2025-11-18 16:07:01	\N
\.


--
-- TOC entry 3724 (class 0 OID 41118)
-- Dependencies: 267
-- Data for Name: student_trimestre_averages; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.student_trimestre_averages (id, student_id, trimestre, average, rank, academic_year_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3700 (class 0 OID 32905)
-- Dependencies: 243
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.students (id, first_name, last_name, birthdate, registration_number, created_at, updated_at, gender, num_educ, birth_place, parent_phone, school_fees_paid, fully_paid, validated, registration_fee, is_validated, amount_paid, school_fees, academic_year_id, birth_date, entity_id, class_id, birth_certificate, vaccination_card, previous_report_card, diploma_certificate, parent_full_name, parent_email, age) FROM stdin;
15	Patric	AGOSSOUKPE	\N	\N	2025-10-29 13:28:50	2025-10-29 13:28:51	M	02	Abomey Calavi	01 96 99 53 04	0.00	f	f	\N	t	0.00	0.00	1	2021-12-11	1	19	https://res.cloudinary.com/marialain/image/upload/v1761744528/students_files/qwrmtyi5oo9okvkwm9vy.jpg	https://res.cloudinary.com/marialain/image/upload/v1761744530/students_files/u2uh3pwv5xxbozayk2mg.pdf	\N	\N	AGOSSOUKPE Ulrich	houndokinnouhotegnidiegue@gamail.com	-3.88
16	Salomé	AGBO	\N	\N	2025-10-29 13:31:00	2025-10-29 13:31:01	F	03	Calavi	01 97 50 67 42	0.00	f	f	\N	t	0.00	0.00	1	2022-01-22	1	19	https://res.cloudinary.com/marialain/image/upload/v1761744658/students_files/vptc1q7ejxlzgigjqnnd.jpg	https://res.cloudinary.com/marialain/image/upload/v1761744659/students_files/ihzpa9y0nb71bpinzuer.pdf	\N	\N	AGBOB Junior	houndokinnouhotegnidiegue@gamail.com	-3.77
17	Théophile Rachid	TCHACON	\N	\N	2025-10-29 13:33:23	2025-10-29 13:33:24	M	04	Bantè	01 61 33 51 75	0.00	f	f	\N	t	0.00	0.00	1	2020-12-08	1	19	https://res.cloudinary.com/marialain/image/upload/v1761744801/students_files/urig4zkvklfcicwe58ec.jpg	https://res.cloudinary.com/marialain/image/upload/v1761744803/students_files/co7cuadmef2qb1etg8mn.pdf	\N	\N	TCHACON Edouard	houndokinnouhotegnidiegue@gamail.com	-4.89
18	Bénie	OTCHO	\N	\N	2025-10-29 13:36:26	2025-10-29 13:36:27	F	05	Ouèdo	01 97 61 58 82	0.00	f	f	\N	t	0.00	0.00	1	2020-12-18	1	19	https://res.cloudinary.com/marialain/image/upload/v1761744983/students_files/gsdi7tfbrublokerwysk.jpg	https://res.cloudinary.com/marialain/image/upload/v1761744985/students_files/woofqwq83p1nkc334hcr.pdf	\N	\N	OTCHO Ganiou	houndokinnouhotegnidiegue@gamail.com	-4.86
19	Mahoutin Martin Godson	HOUENOU	\N	\N	2025-10-29 13:38:55	2025-10-29 13:38:56	M	06	Abomey Calavi	01 97 95 24 53	0.00	f	f	\N	t	0.00	0.00	1	2021-01-30	1	19	https://res.cloudinary.com/marialain/image/upload/v1761745133/students_files/so3ftthbhwmhf5m4sebd.jpg	https://res.cloudinary.com/marialain/image/upload/v1761745134/students_files/b1x7mwhzrr24m8bsfjei.pdf	\N	\N	HOUNENOU Moise	houndokinnouhotegnidiegue@gamail.com	-4.75
20	Ange Marie Kylian	GAHOUE	\N	\N	2025-10-29 13:41:24	2025-10-29 13:41:25	M	07	Abomey Calavi	01 66 87 81 67	0.00	f	f	\N	t	0.00	0.00	1	2021-12-12	1	19	https://res.cloudinary.com/marialain/image/upload/v1761745282/students_files/yi5jcunrgq8qr335hxdc.jpg	https://res.cloudinary.com/marialain/image/upload/v1761745283/students_files/t3eue6aexis2hiye0ncs.pdf	\N	\N	YEKPE Yasmine	houndokinnouhotegnidiegue@gamail.com	-3.88
21	Darell	DJIDONOU	\N	\N	2025-10-29 13:43:50	2025-10-29 13:43:51	M	08	Abomey Calavi	01 97 38 91 95	0.00	f	f	\N	t	0.00	0.00	1	2021-10-01	1	19	https://res.cloudinary.com/marialain/image/upload/v1761745428/students_files/uztjqw2nefttzdfb7exk.jpg	https://res.cloudinary.com/marialain/image/upload/v1761745430/students_files/zvh4p6hiz3exctytqfvu.pdf	\N	\N	DJIDONOU Jérôme	houndokinnouhotegnidiegue@gamail.com	-4.08
22	Pricillia Lydi Degnon	DAGBO	\N	\N	2025-10-29 13:46:13	2025-10-29 13:46:13	F	09	Cotonou	01 96 83 10 45	0.00	f	f	\N	t	0.00	0.00	1	2021-08-18	1	19	https://res.cloudinary.com/marialain/image/upload/v1761745571/students_files/ymthkemhaorl4tansjza.jpg	https://res.cloudinary.com/marialain/image/upload/v1761745572/students_files/rbethqsenifpuqvlgmpx.pdf	\N	\N	DAGBO Roméo	houndokinnouhotegnidiegue@gamail.com	-4.20
23	Prince Maxime	ANIANKOSSAN	\N	\N	2025-10-29 13:48:59	2025-10-29 13:49:00	M	10	Cotonou	01 51 12 46 68	0.00	f	f	\N	t	0.00	0.00	1	2021-03-24	1	19	https://res.cloudinary.com/marialain/image/upload/v1761745738/students_files/qqp05dnib1k3c8ru23cb.jpg	https://res.cloudinary.com/marialain/image/upload/v1761745739/students_files/iq9jeujekjauoxor0hgi.pdf	\N	\N	ANIANKOSSOU Modeste	houndokinnouhotegnidiegue@gamail.com	-4.60
24	Prunelle	ADONNAGBO	\N	\N	2025-10-29 13:51:45	2025-10-29 13:51:46	F	11	Tankpè	01 64 15 15 45	0.00	f	f	\N	t	0.00	0.00	1	2021-06-01	1	19	https://res.cloudinary.com/marialain/image/upload/v1761745903/students_files/bciojspbqxh78dbaxm37.jpg	https://res.cloudinary.com/marialain/image/upload/v1761745904/students_files/fjb8n8tygigzckbqfkm3.pdf	\N	\N	ADONNAGBO Patrice	houndokinnouhotegnidiegue@gamail.com	-4.41
25	Senadoh Rosette	TOSSOU	\N	\N	2025-10-29 13:54:57	2025-10-29 13:54:58	F	12	Bantè	01 97 01 93 63	0.00	f	f	\N	t	0.00	0.00	1	2021-12-30	1	19	https://res.cloudinary.com/marialain/image/upload/v1761746096/students_files/iuz7v7rjxfmm9ybxquvt.jpg	https://res.cloudinary.com/marialain/image/upload/v1761746097/students_files/kq5w5ueg1ndytq1qg0bb.pdf	\N	\N	TOSSOU Guerin	houndokinnouhotegnidiegue@gamail.com	-3.83
26	Inaya Lumière	KINDA	\N	\N	2025-10-29 13:58:00	2025-10-29 13:58:00	F	13	CHU Ab Calavi	01 95 60 57 11	0.00	f	f	\N	t	0.00	0.00	1	2022-12-15	1	18	https://res.cloudinary.com/marialain/image/upload/v1761746278/students_files/womldthfmxp16m3yc7yo.jpg	https://res.cloudinary.com/marialain/image/upload/v1761746279/students_files/bj2ervhg2onqaxyuzhy4.pdf	\N	\N	VODOUNON KINDA Armel	armelkinda@gmail.com	-2.87
27	OMOLOTO Marie Justine	DOSSOU	\N	\N	2025-10-29 14:00:24	2025-10-29 14:00:25	F	14	Cotonou	01 95 05 36 72	0.00	f	f	\N	t	0.00	0.00	1	2021-03-15	1	18	https://res.cloudinary.com/marialain/image/upload/v1761746421/students_files/mj1uym9qtncmnhbgq4s6.jpg	https://res.cloudinary.com/marialain/image/upload/v1761746423/students_files/dd20weorg3ytc8ch5xco.pdf	\N	\N	DOSSOU KISITO	houndokinnouhotegnidiegue@gamail.com	-4.63
28	Amiel	TGBELE	\N	\N	2025-10-29 14:04:40	2025-10-29 14:04:41	M	15	Calavi	01 67 41 81 60	0.00	f	f	\N	t	0.00	0.00	1	2020-10-08	2	13	https://res.cloudinary.com/marialain/image/upload/v1761746680/students_files/lwiequmgu7df8ezj924p.jpg	\N	\N	\N	TEGBLE Wilfried	houndokinnouhotegnidiegue@gamail.com	-5.06
29	Grâce Bénédicte Ayôdélé Sarah	SOUDE	\N	\N	2025-10-29 14:08:07	2025-10-29 14:08:07	F	16	Ab	01 97 74 96 43	0.00	f	f	\N	t	0.00	0.00	1	2020-07-17	2	13	https://res.cloudinary.com/marialain/image/upload/v1761746886/students_files/ilj4eqjzn0ohwb3yhp5o.jpg	\N	\N	\N	SOUDE Léon	leonsoude@gmail.com	-5.29
30	Arnold Marie Fènou	TCHANKPAN	\N	\N	2025-10-29 14:12:31	2025-10-29 14:12:32	F	17	Cotonou	01 66 67 53 54	0.00	f	f	\N	t	0.00	0.00	1	2020-05-12	2	13	https://res.cloudinary.com/marialain/image/upload/v1761747151/students_files/hutyis57qeyrbmmtnuvs.jpg	\N	\N	\N	TCHANKPAN Franchisco	emanold90@gmail.com	-5.47
31	Lovely Flora	HOUNKANRIN	\N	\N	2025-10-29 14:16:10	2025-10-29 14:16:11	M	18	Cotonou	01 96 19 88 86	0.00	f	f	\N	t	0.00	0.00	1	2020-12-09	2	13	https://res.cloudinary.com/marialain/image/upload/v1761747369/students_files/pl4xfittushh4rf5pvkz.jpg	\N	\N	\N	HOUNKANRIN Rolland	houndokinnouhotegnidiegue@gamail.com	-4.89
32	Floride	ANATO	\N	\N	2025-10-29 14:18:15	2025-10-29 14:18:15	F	19	Abomey Calavi	01 95 23 23 09	0.00	f	f	\N	t	0.00	0.00	1	2020-07-14	2	13	https://res.cloudinary.com/marialain/image/upload/v1761747494/students_files/fjtjl4px3cnt2gas1h99.jpg	\N	\N	\N	ANATO Roméo	anatoromeo5@gmail.com	-5.29
12	Ethan Anderson	CHACHA	\N	\N	2025-10-29 10:31:31	2025-10-29 15:30:10	M	00	Cotonou Agla	0197507343	0.00	f	f	\N	t	0.00	\N	1	2022-09-01	1	18	https://res.cloudinary.com/marialain/image/upload/v1761733890/students_files/pmga7ugffzav6l85kttp.jpg	\N	\N	\N	CHACHA Dios Milckel	chados90@gmail.com	-3.16
33	Nathanael	AZONSI	\N	\N	2025-10-29 14:20:57	2025-10-29 14:20:57	M	20	Houègbo	01 96 97 69 97	0.00	f	f	\N	t	0.00	0.00	1	2020-05-08	2	13	https://res.cloudinary.com/marialain/image/upload/v1761747656/students_files/bu68ltjdwsul6z6wd6k9.jpg	\N	\N	\N	AZONSI Amour	amourazonsi@gmail.com	-5.48
34	Daniel Jeean Boxeau	ATOLA	\N	\N	2025-10-29 14:23:38	2025-10-29 14:23:38	M	21	Cotonou	01 65 21 69 65	0.00	f	f	\N	t	0.00	0.00	1	2021-01-15	2	13	https://res.cloudinary.com/marialain/image/upload/v1761747817/students_files/rhpzqhocask1ldoqugyy.jpg	\N	\N	\N	ATOLA Hylarion	houndokinnouhotegnidiegue@gamail.com	-4.79
35	Daniel Magloire	AMOUSSOU	\N	\N	2025-10-29 14:25:26	2025-10-29 14:25:27	M	22	Cotonou	01 97 14 25 84	0.00	f	f	\N	t	0.00	0.00	1	2020-09-14	2	13	https://res.cloudinary.com/marialain/image/upload/v1761747926/students_files/kk6fkchechnykp5mmpxs.jpg	\N	\N	\N	AMMOUSSOU Eric	houndokinnouhotegnidiegue@gamail.com	-5.12
36	Sosthene	AHOHOUI	\N	\N	2025-10-29 14:27:24	2025-10-29 14:27:25	M	23	Cotonou	01 66 82 65 23	0.00	f	f	\N	t	0.00	0.00	1	2021-01-14	2	13	https://res.cloudinary.com/marialain/image/upload/v1761748044/students_files/gkjmpccnc98etock8v7i.jpg	\N	\N	\N	AHOHOUI Pascal	houndokinnouhotegnidiegue@gamail.com	-4.79
37	Esther Sènami	AHOGNI	\N	\N	2025-10-29 14:29:32	2025-10-29 14:29:32	M	24	Calavi	01 90 68 35 78	0.00	f	f	\N	t	0.00	0.00	1	2020-09-18	2	13	https://res.cloudinary.com/marialain/image/upload/v1761748171/students_files/ls5aci5dmwnhkdxpx5bb.jpg	\N	\N	\N	AHOGNI Théodore	houndokinnouhotegnidiegue@gamail.com	-5.11
38	Aden Jufovic	AGUETE	\N	\N	2025-10-29 14:42:39	2025-10-29 14:42:40	M	25	Abomey Calavi	01 67 58 52 17	0.00	f	f	\N	t	0.00	0.00	1	2020-11-05	2	13	https://res.cloudinary.com/marialain/image/upload/v1761748958/students_files/w3vookrtyiqoi8jsv2jq.jpg	\N	\N	\N	AGUETE Didier	aguetedidier@gamail.com	-4.98
39	Rachid Toundé	ABOUDOU	\N	\N	2025-10-29 14:44:13	2025-10-29 14:44:14	M	26	Calavi	01 97 05 44 11	0.00	f	f	\N	t	0.00	0.00	1	2020-03-20	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749053/students_files/xgra1i9xw5i9unjmcigv.jpg	\N	\N	\N	ABOUDOU Aliou	houndokinnouhotegnidiegue@gamail.com	-5.61
40	Bona Rayath	ABDOULAYE	\N	\N	2025-10-29 14:46:19	2025-10-29 14:46:20	F	27	Cotonou	01 97 01 26 88	0.00	f	f	\N	t	0.00	0.00	1	2020-08-11	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749179/students_files/xdsc0dbxly9szyzhznpx.jpg	\N	\N	\N	Abdoulaye Abdel	houndokinnouhotegnidiegue@gamail.com	-5.22
41	Noam	AVODAGBE	\N	\N	2025-10-29 14:48:54	2025-10-29 14:48:55	M	28	Cotonou	01 67 04 05 47	0.00	f	f	\N	t	0.00	0.00	1	2020-08-13	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749334/students_files/depxaabhpdtevsbbg9mx.jpg	\N	\N	\N	AVODAGBE Clavers	avodagbeclavers24@gmail.com	-5.21
42	Romamir Adouke	D'ALMEIDA	\N	\N	2025-10-29 14:52:16	2025-10-29 14:52:17	F	29	Cotonou	01 97 05 16 37	0.00	f	f	\N	t	0.00	0.00	1	2020-02-28	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749535/students_files/b0dbuhqv75xuwe4ririr.jpg	\N	\N	\N	D'ALMEIDAH ELISEE	houndokinnouhotegnidiegue@gamail.com	-5.67
43	Hillary Mia	CHACHA	\N	\N	2025-10-29 14:54:14	2025-10-29 14:54:15	F	30	Cotonou Agla	01 97 50 67 42	0.00	f	f	\N	t	0.00	0.00	1	2020-07-02	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749653/students_files/wdgr7hufo5fa0yjrdklo.jpg	\N	\N	\N	CHACHA Dios Milckel	chados90@gmail.com	-5.33
44	Edem Isaac	DEGBE	\N	\N	2025-10-29 14:56:28	2025-10-29 14:56:29	M	31	Adja	01 62 67 72 97	0.00	f	f	\N	t	0.00	0.00	1	2020-10-25	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749788/students_files/q7ut7udfcmjsbkmufpuk.jpg	\N	\N	\N	DEGBE Edmond	houndokinnouhotegnidiegue@gamail.com	-5.01
45	Maurice	FAKEYE	\N	\N	2025-10-29 14:59:03	2025-10-29 14:59:04	M	32	Abomey Calavi	01 67 52 12 43	0.00	f	f	\N	t	0.00	0.00	1	2021-03-28	2	13	https://res.cloudinary.com/marialain/image/upload/v1761749942/students_files/dtgjcihquuioixjcnnuq.jpg	\N	\N	\N	FAKEYE Paul	kollawo@yahoo.fr	-4.59
46	Béni Nathan	GANKPAN	\N	\N	2025-10-29 15:01:44	2025-10-29 15:01:45	M	33	Abomey Calavi	01 96 30 11 12	0.00	f	f	\N	t	0.00	0.00	1	2020-05-19	2	13	https://res.cloudinary.com/marialain/image/upload/v1761750104/students_files/lk5jjshsodundyrwaprt.jpg	\N	\N	\N	GANKPAN Sylvain	sgankpansylvain@gmail.com	-5.45
47	Marie Joy	GODONOU	\N	\N	2025-10-29 15:03:26	2025-10-29 15:03:27	F	34	Abomey Calavi	01 97 50 67 42	0.00	f	f	\N	t	0.00	0.00	1	2021-07-21	2	13	https://res.cloudinary.com/marialain/image/upload/v1761750205/students_files/jvxzr5a51cqtm4gx0lma.jpg	\N	\N	\N	GODONOU Ambroise	chados90@gmail.com	-4.28
48	Rahim	IDRISSOU	\N	\N	2025-10-29 15:05:56	2025-10-29 15:05:57	M	35	AITCHEDJI	01 48 52 97 41	0.00	f	f	\N	t	0.00	0.00	1	2021-12-04	2	13	https://res.cloudinary.com/marialain/image/upload/v1761750356/students_files/vdwdvl1fo1aseaacenln.jpg	\N	\N	\N	idrissou aBDOU	cdhfjfjc@gmail.com	-3.90
49	Jenna	KIMSICLOUMON	\N	\N	2025-10-29 15:09:16	2025-10-29 15:09:17	M	36	Abomey Calavi	01 66 74 26 07	0.00	f	f	\N	t	0.00	0.00	1	2020-08-12	2	13	https://res.cloudinary.com/marialain/image/upload/v1761750556/students_files/mhkffmayxgoi0roy2ruu.jpg	\N	\N	\N	KINSICLOUNON Cossi	kinsiklounonpatrice36@gmail.com	-5.22
50	Perside Bénie	KOHONOU	\N	\N	2025-10-29 15:14:36	2025-10-29 15:14:37	F	37	Adéola Senadé	01 97 83 85 80	0.00	f	f	\N	t	0.00	0.00	1	2021-03-03	2	13	https://res.cloudinary.com/marialain/image/upload/v1761750876/students_files/f11ws1mcauenroyjgycm.jpg	\N	\N	\N	KOHONOU Raoul	kohonou82@gmail.com	-4.66
51	Josaphat Oluwa-Gbemiga	KOTTIN	\N	\N	2025-10-29 15:18:43	2025-10-29 15:18:44	M	38	Abomey Calavi	01 96 81 74 57	0.00	f	f	\N	t	0.00	0.00	1	2021-03-25	2	13	https://res.cloudinary.com/marialain/image/upload/v1761751123/students_files/ohmcvmkrqtlegsj93rbs.jpg	\N	\N	\N	KOTTIN Paul	ukkottin@gmail.com	-4.60
52	Maeva Essenam	MIGNAGANDO	\N	\N	2025-10-29 15:22:06	2025-10-29 15:22:06	F	39	Cotonou	01 96 21 84 60	0.00	f	f	\N	t	0.00	0.00	1	2020-12-20	2	13	https://res.cloudinary.com/marialain/image/upload/v1761751325/students_files/u4b5pv7qjbdekcgwnsr2.jpg	\N	\N	\N	MIGNIGANDO Fidèle	leonsoude@gmail.com	-4.86
53	Ulric Tunde	SOHE	\N	\N	2025-10-29 15:24:17	2025-10-29 15:24:18	M	40	Cotonou	01 96 49 37 70	0.00	f	f	\N	t	0.00	0.00	1	2020-09-01	2	13	https://res.cloudinary.com/marialain/image/upload/v1761751457/students_files/h9ev0joghfey5a1wzofd.jpg	\N	\N	\N	SOKE Gabriel	sgankpansylvain@gmail.com	-5.16
54	Mael Exaucé Jesugnon	ZEHOUNKPE	\N	\N	2025-10-29 15:26:46	2025-10-29 15:26:47	M	41	Cotonou	01 46 34 34 11	0.00	f	f	\N	t	0.00	0.00	1	2020-03-27	2	13	https://res.cloudinary.com/marialain/image/upload/v1761751606/students_files/zocihszrlunv4df7hpbm.jpg	\N	\N	\N	ZEHOUKPE Gildas	houndokinnouhotegnidiegue@gamail.com	-5.59
55	Michée	CHEMMEDE	\N	\N	2025-10-29 15:43:46	2025-10-29 15:43:47	M	42	Abomey Calavi	01 97 50 43 10	0.00	f	f	\N	t	0.00	0.00	1	2019-05-22	2	2	https://res.cloudinary.com/marialain/image/upload/v1761752626/students_files/ljf52pzdnlp82ehpr2lv.jpg	\N	\N	\N	CHEMMEDE Samuel	samuelchemmede9750@gmail.com	-6.44
56	Lahora Ysis Charbie	KINDA	\N	\N	2025-10-29 15:47:05	2025-10-29 15:47:06	M	43	CHU Ab Calavi	01 95 60 57 11	0.00	f	f	\N	t	0.00	0.00	1	2019-06-25	2	2	https://res.cloudinary.com/marialain/image/upload/v1761752824/students_files/ko3lzrkccg3bq6co8jeh.jpg	\N	\N	\N	VODOUNON KINDA Armel	armelkinda69@gmail.com	-6.35
57	Trinité	D'ALMEIDA	\N	\N	2025-10-29 15:49:52	2025-10-29 15:49:53	M	44	Cotonou	01 97 05 16 37	0.00	f	f	\N	t	0.00	0.00	1	2018-03-21	2	2	https://res.cloudinary.com/marialain/image/upload/v1761752991/students_files/eoxkgv6lzleyfhu3uwc7.jpg	\N	\N	\N	D'ALMEIDAH ELISEE HERVE	wilevas@gmail.com	-7.61
59	Happy Jean	HOUENOU	\N	\N	2025-10-29 16:06:58	2025-10-29 16:06:58	M	46	Abomey Calavi	01 97 95 24 53	0.00	f	f	\N	t	0.00	0.00	1	2019-05-12	2	2	https://res.cloudinary.com/marialain/image/upload/v1761754017/students_files/onm5gruuetqav5oylfis.jpg	\N	\N	\N	HOUENOU Moise	houenougodonou@gmail.com	-6.47
60	Junior Kolawolé	FAKEYE	\N	\N	2025-10-29 16:09:33	2025-10-29 16:09:34	M	47	Abomey Calavi	01 67 52 12 43	0.00	f	f	\N	t	0.00	0.00	1	2018-10-26	2	2	https://res.cloudinary.com/marialain/image/upload/v1761754173/students_files/lw6vwvpqbhcnzib75ubp.jpg	\N	\N	\N	FAKEYE Paul	kohonou82@gmail.com	-7.01
61	Lioneil Gériel Raphael	ASSOGBA HEDOUGBEKOUN	\N	\N	2025-10-29 16:17:23	2025-10-29 16:17:24	M	48	Abomey Calavi	01 96 22 37 12	0.00	f	f	\N	t	0.00	0.00	1	2018-10-23	2	2	https://res.cloudinary.com/marialain/image/upload/v1761754643/students_files/b1yo9rs7p8jwv6ly5mjn.jpg	\N	\N	\N	ASSOGBA Roméo	houndokinnouhotegnidiegue@gamail.com	-7.02
62	Soussouni Stéphie	BOCOVO	\N	\N	2025-10-29 16:38:45	2025-10-29 16:38:46	M	49	Cotonou	01 95 57 69 57	0.00	f	f	\N	t	0.00	0.00	1	2020-01-14	2	2	https://res.cloudinary.com/marialain/image/upload/v1761755925/students_files/n0zn2sp2jt2amqy0vigy.jpg	\N	\N	\N	BOCOVO Finagnon	bocovomarius@gmail.com	-5.79
63	Farouk	ABOUDOU	\N	\N	2025-10-29 16:42:21	2025-10-29 16:42:21	F	50	Calavi	01 97 05 44 11	0.00	f	f	\N	t	0.00	0.00	1	2018-01-18	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756140/students_files/cyzunpjsbwlmj32yngpj.jpg	\N	\N	\N	ABOUDOU Aliou	adboualiou@gmail.com	-7.78
64	Widad	AMINOU	\N	\N	2025-10-29 16:43:54	2025-10-29 16:43:55	F	51	Porto Novo	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-09-21	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756234/students_files/dhaioxqex0rfucvs3rqg.jpg	\N	\N	\N	AMINOU Adio	houndokinnouhotegnidiegue@gamail.com	-9.11
65	Hugues	ZONON	\N	\N	2025-10-29 16:45:46	2025-10-29 16:45:46	M	52	Calavi	01 97 14 14 13	0.00	f	f	\N	t	0.00	0.00	1	2020-01-06	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756345/students_files/rbsi1ms9e4xjzegynnic.jpg	\N	\N	\N	ZONON LEON	houndokinnouhotegnidiegue@gamail.com	-5.81
66	Barikatou	IDRISSOU	\N	\N	2025-10-29 16:48:17	2025-10-29 16:48:17	F	53	AITCHEDJI	01 48 52 27 41	0.00	f	f	\N	t	0.00	0.00	1	2018-08-30	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756496/students_files/iglbv2tbm9xxshax53lc.jpg	\N	\N	\N	IDRISSOU Abdou Falilou	etisounnouvou@gmail.com	-7.17
67	Samuel	ATCHIDI	\N	\N	2025-10-29 16:50:14	2025-10-29 16:50:14	M	54	Cotonou	01 40 74 21 64	0.00	f	f	\N	t	0.00	0.00	1	2019-09-06	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756613/students_files/jx3zyzelmfm7mw6e6dcp.jpg	\N	\N	\N	ATHIDI Alain	atchidialain@gmail.com	-6.15
68	Abèhoun Béatrice	DOSSOU	\N	\N	2025-10-29 16:52:17	2025-10-29 16:52:17	F	55	Abomey	01 66 99 46 32	0.00	f	f	\N	t	0.00	0.00	1	2020-02-10	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756736/students_files/zdfi4rzyhefq7a4x9tvj.jpg	\N	\N	\N	DOSSOU KISITO	kinsiklounonpatrice36@gmail.com	-5.72
69	Evens	CHODATON	\N	\N	2025-10-29 16:54:21	2025-10-29 16:54:22	M	56	Cotonou	01 97 87 32 23	0.00	f	f	\N	t	0.00	0.00	1	2019-09-23	2	2	https://res.cloudinary.com/marialain/image/upload/v1761756861/students_files/nhcpl9dtlgfbsvd9rqw5.jpg	\N	\N	\N	CHODATON Edound	houndokinnouhotegnidiegue@gamail.com	-6.10
70	Kéliane	KPAKPO	\N	\N	2025-10-29 16:57:11	2025-10-29 16:57:12	F	57	Cotonou	01 96 75 68 72	0.00	f	f	\N	t	0.00	0.00	1	2019-12-05	2	2	https://res.cloudinary.com/marialain/image/upload/v1761757031/students_files/oabr0mxvx1zcfzsvd2ix.jpg	\N	\N	\N	KPAKPO Salomon	houndokinnouhotegnidiegue@gamail.com	-5.90
71	OMAR	MOUMOUNI	\N	\N	2025-10-29 17:02:08	2025-10-29 17:02:09	M	58	Akpakpa	01 96 46 75 52	0.00	f	f	\N	t	0.00	0.00	1	2019-08-14	2	2	https://res.cloudinary.com/marialain/image/upload/v1761757328/students_files/l1gx0wrvnueyba46rxdr.jpg	\N	\N	\N	MOUMOUNI	etisounnouvou@gmail.com	-6.21
14	Akonassou Marie Reine	SOUNNOUVOU	\N	\N	2025-10-29 13:25:47	2025-10-29 17:04:24	F	01	Abomey Calavi	01 96 46 75 52	0.00	f	f	\N	t	0.00	0.00	1	2021-05-12	1	19	https://res.cloudinary.com/marialain/image/upload/v1761744345/students_files/yj5ubyc7nf6pqmd8ydbh.jpg	https://res.cloudinary.com/marialain/image/upload/v1761744346/students_files/h7jkxo4km9vam36x3qow.pdf	\N	\N	SOUNNOUVOU Etienne	etisounnouvou@gmail.com	-4.47
58	Bignono Prunelle Marielle	GNAHA	\N	\N	2025-10-29 15:53:53	2025-10-29 17:09:14	M	45	Abomey Calavi	01 96 14 59 58	0.00	f	f	\N	t	0.00	\N	1	2020-03-09	2	2	https://res.cloudinary.com/marialain/image/upload/v1761753232/students_files/forvwdgfsqtnp8ebuwwe.jpg	\N	\N	\N	GNAHA Mariano	mgha@yahoo.fr	-5.64
72	Mikda	ACLASSATO	\N	\N	2025-10-30 18:37:43	2025-10-30 18:37:44	M	60	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-05-08	2	14	https://res.cloudinary.com/marialain/image/upload/v1761849463/students_files/jgilsde2ww6s6njuxoks.jpg	\N	\N	\N	Na	na@gmail.com	-7.48
73	Emmanuel	AGBO	\N	\N	2025-10-30 18:40:15	2025-10-30 18:40:15	M	61	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-12-25	2	14	https://res.cloudinary.com/marialain/image/upload/v1761849614/students_files/u62qgeonw4xllytoghsv.jpg	\N	\N	\N	Na	na@gmail.com	-6.85
74	Sètondji Pierre-Gormeille Junior	BADE	\N	\N	2025-10-31 02:45:30	2025-10-31 02:45:31	M	62	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-09-13	2	14	https://res.cloudinary.com/marialain/image/upload/v1761878730/students_files/kkuhpjqbfjludhaxdqto.jpg	\N	\N	\N	Na	na@gmail.com	-7.13
75	Sètondji Pierre-Corneille Junior	BADE	\N	\N	2025-10-31 19:26:01	2025-10-31 19:26:02	M	63	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-09-13	2	14	https://res.cloudinary.com/marialain/image/upload/v1761938760/students_files/j8fxuqzy7szlm4tig5vr.jpg	\N	\N	\N	Na	na@gmail.com	-7.13
76	Djibril	BATIMON ALI	\N	\N	2025-10-31 19:28:38	2025-10-31 19:28:39	M	64	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2019-03-28	2	14	https://res.cloudinary.com/marialain/image/upload/v1761938918/students_files/niy13nncqmtepfwewdu6.jpg	\N	\N	\N	Na	na@gmail.com	-6.60
77	Amadis Dagbedji Benoit	HIDJO	\N	\N	2025-10-31 19:32:00	2025-10-31 19:32:00	M	65	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-07-11	2	14	https://res.cloudinary.com/marialain/image/upload/v1761939119/students_files/oxyz5m5ytaftjd6eimyl.jpg	\N	\N	\N	Na	na@gmail.com	-7.31
78	Steven	HOUANGNI	\N	\N	2025-10-31 19:37:37	2025-10-31 19:37:37	M	66	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-07-08	2	14	https://res.cloudinary.com/marialain/image/upload/v1761939456/students_files/vxui0ifmot2biy61tcpf.jpg	\N	\N	\N	Na	na@gmail.com	-7.32
79	Veronica	KOHLA	\N	\N	2025-10-31 19:40:06	2025-10-31 19:40:07	F	67	Akpakpa	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-10-19	2	14	https://res.cloudinary.com/marialain/image/upload/v1761939606/students_files/jwwlz24acxuyfl4tv6w3.jpg	\N	\N	\N	Na	na@gmail.com	-7.04
80	Miracle	KOTTIN	\N	\N	2025-10-31 19:43:59	2025-10-31 19:43:59	F	68	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-10-13	2	14	https://res.cloudinary.com/marialain/image/upload/v1761939838/students_files/g8kihmqbvpwjnis5kn40.jpg	\N	\N	\N	Na	na@gmail.com	-7.05
81	Maria Constance Olive	TONEGNIKES	\N	\N	2025-10-31 19:50:48	2025-10-31 19:50:49	F	69	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2019-03-03	2	14	https://res.cloudinary.com/marialain/image/upload/v1761940247/students_files/bnqxfqnfclp3m1cpav3h.jpg	\N	\N	\N	Na	na@gmail.com	-6.67
82	Samuel	TOSSOU	\N	\N	2025-10-31 19:53:03	2025-10-31 19:53:04	M	70	Bantè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-10-01	2	14	https://res.cloudinary.com/marialain/image/upload/v1761940383/students_files/irjczprv4tt8w0jqeq2z.jpg	\N	\N	\N	Na	na@gmail.com	-8.08
83	Trésor	TCHAOU	\N	\N	2025-10-31 20:04:35	2025-10-31 20:04:35	M	1190324003037	Aïtchedji	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2019-07-16	2	14	https://res.cloudinary.com/marialain/image/upload/v1761941074/students_files/rdybrobh8fgovneryduq.jpg	\N	\N	\N	Na	na@gmail.com	-6.30
84	Maria Anicia	TCHANKPAN	\N	\N	2025-10-31 20:08:21	2025-10-31 20:08:22	F	71	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-05-09	2	14	https://res.cloudinary.com/marialain/image/upload/v1761941300/students_files/rphpul4c9zgitwnl9ujj.jpg	\N	\N	\N	Na	na@gmail.com	-7.48
102	Précieux	SEHA	\N	\N	2025-11-01 20:43:55	2025-11-01 20:43:55	F	116	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2008-10-04	3	10	https://res.cloudinary.com/marialain/image/upload/v1762029834/students_files/sgzwabhc122tv2tyhepa.jpg	\N	\N	\N	NA	Na@gmail.com	-17.08
86	Rhonel Samuel OLUWATOBI	ODJO	\N	\N	2025-11-01 19:56:56	2025-11-01 19:56:56	M	100	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-08-14	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027015/students_files/aojbufit2853bbrnvolo.jpg	\N	\N	\N	NA	Na@gmail.com	-15.22
87	Fleurette Amatsia	KPAKPO	\N	\N	2025-11-01 19:59:40	2025-11-01 19:59:41	F	101	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-07-25	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027179/students_files/pzdnegikmktnrf7lawii.jpg	\N	\N	\N	NA	Na@gmail.com	-15.27
88	H.Marc-Antoine Yanis	HIDJO	\N	\N	2025-11-01 20:02:04	2025-11-01 20:02:04	M	102	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-04-13	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027323/students_files/uarojbfnu9pcu04u5ppx.jpg	\N	\N	\N	NA	Na@gmail.com	-15.56
89	Amado	LIONFIN	\N	\N	2025-11-01 20:04:15	2025-11-01 20:04:16	M	103	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2008-11-26	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027455/students_files/gihfir7wkirjanqkwonq.jpg	\N	\N	\N	NA	Na@gmail.com	-16.93
90	Fréjus	DONOU	\N	\N	2025-11-01 20:05:50	2025-11-01 20:05:51	M	104	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-12-29	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027550/students_files/cuf0xt7bbajruugpevrg.jpg	\N	\N	\N	NA	Na@gmail.com	-15.84
91	Grâce	GOMENOU	\N	\N	2025-11-01 20:07:46	2025-11-01 20:07:47	F	105	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-11-17	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027666/students_files/nk9sv0z5mdlzbknfjaty.jpg	\N	\N	\N	NA	Na@gmail.com	-14.96
92	Inès	ANAGOSSI	\N	\N	2025-11-01 20:10:32	2025-11-01 20:10:33	F	106	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-09-10	3	9	https://res.cloudinary.com/marialain/image/upload/v1762027832/students_files/hqxulvimx3wkrvq1ixmv.jpg	\N	\N	\N	NA	Na@gmail.com	-16.14
93	Sylvie Marthe	GANKPAN	\N	\N	2025-11-01 20:14:53	2025-11-01 20:14:54	F	107	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-10-31	3	9	https://res.cloudinary.com/marialain/image/upload/v1762028092/students_files/b3i20iihtrmsinqu0sdv.jpg	\N	\N	\N	NA	Na@gmail.com	-15.01
94	Sylvia Ruth	GANKPAN	\N	\N	2025-11-01 20:16:43	2025-11-01 20:16:43	F	108	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-10-31	3	9	https://res.cloudinary.com/marialain/image/upload/v1762028202/students_files/zyywhoijqu7jhlkkudrg.jpg	\N	\N	\N	NA	Na@gmail.com	-15.01
95	Gbetovivi Aubin	DJOSSOU	\N	\N	2025-11-01 20:18:30	2025-11-01 20:18:31	M	109	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-10-31	3	9	https://res.cloudinary.com/marialain/image/upload/v1762028310/students_files/krgfksqudsmdjcvumwqb.jpg	\N	\N	\N	NA	Na@gmail.com	-15.01
96	Mariam Asake	ADAMA	\N	\N	2025-11-01 20:21:35	2025-11-01 20:21:36	F	110	Nigeria	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-06-29	3	9	https://res.cloudinary.com/marialain/image/upload/v1762028495/students_files/tczb4zamrwbvlrh7rbsi.jpg	\N	\N	\N	NA	Na@gmail.com	-16.34
97	Mariam Asake	ADAMA	\N	\N	2025-11-01 20:25:24	2025-11-01 20:25:24	F	111	Nigeria	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-06-29	3	9	https://res.cloudinary.com/marialain/image/upload/v1762028723/students_files/wxnkzdj7g1k9nq47ymmm.jpg	\N	\N	\N	NA	Na@gmail.com	-16.34
98	Edgard	GOUDE	\N	\N	2025-11-01 20:31:31	2025-11-01 20:31:32	M	112	bantê	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2007-07-08	3	11	https://res.cloudinary.com/marialain/image/upload/v1762029091/students_files/p4ssbllinwdxtzxkrryc.jpg	\N	\N	\N	NA	Na@gmail.com	-18.32
99	Elvira	VODONOU	\N	\N	2025-11-01 20:35:55	2025-11-01 20:35:55	F	113	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2005-11-27	3	10	https://res.cloudinary.com/marialain/image/upload/v1762029354/students_files/hu2dkdub5bl7tn72ydbe.jpg	\N	\N	\N	NA	Na@gmail.com	-19.93
100	Précieux	AYINON	\N	\N	2025-11-01 20:37:42	2025-11-01 20:37:43	M	114	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2008-12-08	3	10	https://res.cloudinary.com/marialain/image/upload/v1762029462/students_files/f6126xmkqwcxahwb0uv0.jpg	\N	\N	\N	NA	Na@gmail.com	-16.90
101	Micrette	BADE	\N	\N	2025-11-01 20:39:49	2025-11-01 20:39:50	F	115	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-05-19	3	10	https://res.cloudinary.com/marialain/image/upload/v1762029589/students_files/fzdinbbwgcfazu9vjqmu.jpg	\N	\N	\N	NA	Na@gmail.com	-15.46
103	Beni Houefa	AWASSI	\N	\N	2025-11-01 20:45:40	2025-11-01 20:45:40	F	117	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-06-20	3	10	https://res.cloudinary.com/marialain/image/upload/v1762029939/students_files/lb23rtmfw6pldj1j9kf3.jpg	\N	\N	\N	Na	Na@gmail.com	-16.37
105	Ezinwe Reine	AYENON	\N	\N	2025-11-01 20:56:14	2025-11-01 20:56:14	F	119	Gouka	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	1999-09-08	3	12	https://res.cloudinary.com/marialain/image/upload/v1762030573/students_files/tf2vrap9mvhft2ialp6f.jpg	\N	\N	\N	NA	Na@gmail.com	-26.15
106	Koudoumou Jeanne	KOBA	\N	\N	2025-11-01 20:58:14	2025-11-01 20:58:15	F	120	Dassa-Zoume	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2002-12-10	3	12	https://res.cloudinary.com/marialain/image/upload/v1762030694/students_files/smsvaxzmxy5n6rqnoubh.jpg	\N	\N	\N	NA	Na@gmail.com	-22.90
107	Mdevi Josué	KOHONOU	\N	\N	2025-11-01 21:00:10	2025-11-01 21:00:10	M	121	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2005-12-01	3	12	https://res.cloudinary.com/marialain/image/upload/v1762030809/students_files/nzx2ic45g2td3mx1yrtb.jpg	\N	\N	\N	NA	Na@gmail.com	-19.92
108	Marie Alphonsine	KINDE	\N	\N	2025-11-01 21:02:10	2025-11-01 21:02:10	F	122	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2008-07-30	3	4	https://res.cloudinary.com/marialain/image/upload/v1762030929/students_files/xkjdbn0s1ypvmlorquyz.jpg	\N	\N	\N	NA	Na@gmail.com	-17.26
109	Lucrèce	GNANVI	\N	\N	2025-11-01 21:03:39	2025-11-01 21:03:39	F	123	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2008-04-01	3	4	https://res.cloudinary.com/marialain/image/upload/v1762031018/students_files/mpkdnlx1mbxjno3dfn29.jpg	\N	\N	\N	NA	Na@gmail.com	-17.59
110	Grâce	DEGAN	\N	\N	2025-11-01 21:04:51	2025-11-01 21:04:52	F	224	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-11-24	3	4	https://res.cloudinary.com/marialain/image/upload/v1762031091/students_files/so9orcugrhikgqzllfel.jpg	\N	\N	\N	NA	Na@gmail.com	-15.94
104	Jovana Phedera	DENOU	\N	\N	2025-11-01 20:54:19	2025-11-02 11:29:32	F	208090160575	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2007-03-27	3	12	https://res.cloudinary.com/marialain/image/upload/v1762030459/students_files/txssjylxzm85mmdld4kp.jpg	\N	\N	\N	NA	Na@gmail.com	-18.60
111	Chabel Précieux	ADANHOUME	\N	\N	2025-11-01 21:07:02	2025-11-01 21:07:03	M	125	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2007-05-17	3	4	https://res.cloudinary.com/marialain/image/upload/v1762031222/students_files/aqlwcopvqpwoqsao9opz.jpg	\N	\N	\N	Na	Na@gmail.com	-18.46
113	Bossima	SABIYERIMA	\N	\N	2025-11-01 21:11:49	2025-11-01 21:11:50	M	127	Natitingou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2005-09-15	3	4	https://res.cloudinary.com/marialain/image/upload/v1762031509/students_files/ibvhmxht3eu5uc8k70zc.jpg	\N	\N	\N	NA	Na@gmail.com	-20.13
114	Youssouf	DZIMAKU	\N	\N	2025-11-02 02:02:26	2025-11-02 02:02:27	M	72	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-01-05	2	14	https://res.cloudinary.com/marialain/image/upload/v1762048945/students_files/k6krzxdcsqg2axte4bis.jpg	\N	\N	\N	Na	na@gmail.com	-10.82
115	Nimata	AGOSSOU-CAKPO	\N	\N	2025-11-02 02:06:05	2025-11-02 02:06:06	F	73	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-06-19	2	14	https://res.cloudinary.com/marialain/image/upload/v1762049165/students_files/c0gadtx48wkhuvcjsahm.jpg	\N	\N	\N	Na	na@gmail.com	-7.37
116	Bidossessi Barachiel	AHO	\N	\N	2025-11-02 02:09:22	2025-11-02 02:09:23	M	74	Bantè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-03-30	2	14	https://res.cloudinary.com/marialain/image/upload/v1762049362/students_files/ddbcdk7wsvpumrfcpaqj.jpg	\N	\N	\N	Na	na@gmail.com	-7.59
117	Généreux Michaël	BABADJODOU	\N	\N	2025-11-02 02:12:49	2025-11-02 02:12:50	M	75	Maternité Cadjehoin	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-09-29	2	14	https://res.cloudinary.com/marialain/image/upload/v1762049569/students_files/zgoafns1c13dxv2bumxt.jpg	\N	\N	\N	Na	na@gmail.com	-7.09
118	Nephtalie Aser	GOUSSANOU	\N	\N	2025-11-02 02:15:29	2025-11-02 02:15:29	M	76	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2019-03-13	2	14	https://res.cloudinary.com/marialain/image/upload/v1762049728/students_files/j4jhrfre9jvdtncq8iac.jpg	\N	\N	\N	Na	na@gmail.com	-6.64
119	Othniel Messi	MONKOUN	\N	\N	2025-11-02 02:17:41	2025-11-02 02:17:42	M	77	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2019-06-02	2	14	https://res.cloudinary.com/marialain/image/upload/v1762049861/students_files/akqhpfm4prbn5cf7gnvs.jpg	\N	\N	\N	Na	na@gmail.com	-6.42
120	Germain Ismaël	OBERIWAI	\N	\N	2025-11-02 02:20:29	2025-11-02 02:20:29	M	78	Hôpital Zou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2019-05-28	2	14	https://res.cloudinary.com/marialain/image/upload/v1762050028/students_files/trv2hnf4nxppyfpvd33b.jpg	\N	\N	\N	Na	na@gmail.com	-6.43
121	Ruth	HOUETON	\N	\N	2025-11-02 02:23:42	2025-11-02 02:23:43	F	79	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-10-01	2	17	https://res.cloudinary.com/marialain/image/upload/v1762050221/students_files/eatcrr5bkpm6lptwrx97.jpg	\N	\N	\N	Na	na@gmail.com	-11.09
122	Blessing Joyce Sewe	SOUNNOUHO	\N	\N	2025-11-02 02:26:24	2025-11-02 02:26:25	F	80	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-07-29	2	17	https://res.cloudinary.com/marialain/image/upload/v1762050384/students_files/glqv3m5kfdm6vmogxiak.jpg	\N	\N	\N	Na	na@gmail.com	-9.26
123	Eric	SIDAISSI	\N	\N	2025-11-02 02:28:22	2025-11-02 02:28:22	M	81	Ouidah	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-11-16	2	17	https://res.cloudinary.com/marialain/image/upload/v1762050501/students_files/hnmntcfcf4hnovrf4nkn.jpg	\N	\N	\N	Na	na@gmail.com	-14.96
124	Abraham	AWONONNON	\N	\N	2025-11-02 02:31:06	2025-11-02 02:31:07	M	82	Godomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-04-27	2	17	https://res.cloudinary.com/marialain/image/upload/v1762050666/students_files/r6axf6iocg9qhyxri5iy.jpg	\N	\N	\N	Na	na@gmail.com	-11.52
125	Lecabelle	AWEKPON	\N	\N	2025-11-02 02:34:51	2025-11-02 02:34:52	F	2140323946545	Tankpè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-08-31	2	17	https://res.cloudinary.com/marialain/image/upload/v1762050891/students_files/mhwqkdxmllq0l5pnuki4.jpg	\N	\N	\N	Na	na@gmail.com	-11.17
126	Eddy Epiphane Kuassi	GNANGNON	\N	\N	2025-11-02 07:42:49	2025-11-02 07:42:50	M	83	Porto Novo	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-01-03	2	17	https://res.cloudinary.com/marialain/image/upload/v1762069369/students_files/p7qxp6cm3kpxjlcf0xyu.jpg	\N	\N	\N	Na	na@gmail.com	-9.83
127	Amar	SALIFOU-KARIM	\N	\N	2025-11-02 07:45:24	2025-11-02 07:45:25	M	84	Parakou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-05-05	2	17	https://res.cloudinary.com/marialain/image/upload/v1762069524/students_files/zlxrwtp57ts3niyxcdwd.jpg	\N	\N	\N	Na	na@gmail.com	-10.50
128	Sènami Judith	HOUENOU	\N	\N	2025-11-02 07:47:37	2025-11-02 07:47:38	F	85	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-05-05	2	17	https://res.cloudinary.com/marialain/image/upload/v1762069657/students_files/fzkiiuwhiavzlxszmige.jpg	\N	\N	\N	Na	na@gmail.com	-9.50
129	Colombe	DADEHOU	\N	\N	2025-11-02 07:50:28	2025-11-02 07:50:29	F	86	Na	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-11-25	2	17	https://res.cloudinary.com/marialain/image/upload/v1762069828/students_files/nctmlqdf7gyrhoq2lngs.jpg	\N	\N	\N	Na	na@gmail.com	-9.94
130	Abdouramane	ALI-BATIMON	\N	\N	2025-11-02 08:26:01	2025-11-02 08:26:02	M	87	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-01-21	2	17	https://res.cloudinary.com/marialain/image/upload/v1762071961/students_files/gt4iwwrj8vg6q4kpdhao.jpg	\N	\N	\N	Na	na@gmail.com	-9.78
131	Trésor	GNAHA	\N	\N	2025-11-02 08:30:36	2025-11-02 08:30:36	F	200	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-03-11	3	3	https://res.cloudinary.com/marialain/image/upload/v1762072236/students_files/hfdlfkmefnyqpddhopqr.jpg	\N	\N	\N	Na	na@gmail.com	-10.65
132	Houefa Michelle Islamiate	FAKAYE	\N	\N	2025-11-02 08:33:16	2025-11-02 08:33:16	F	88	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-08-11	2	17	https://res.cloudinary.com/marialain/image/upload/v1762072395/students_files/hu5atovhjrnpoyxit7g2.jpg	\N	\N	\N	Na	na@gmail.com	-9.23
133	Firdaouss	ACLASSATO	\N	\N	2025-11-02 08:33:31	2025-11-02 08:33:31	F	201	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-31	3	3	https://res.cloudinary.com/marialain/image/upload/v1762072411/students_files/ufp7nuyurxcewg0q7ive.jpg	\N	\N	\N	Na	na@gmail.com	-10.84
137	Laurelle	ADJAHHOUIN	\N	\N	2025-11-02 08:43:51	2025-11-02 09:49:22	F	2140323008303	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-02-23	3	3	https://res.cloudinary.com/marialain/image/upload/v1762073031/students_files/kcyqt2o5mt9ksiag1gyw.jpg	\N	\N	\N	Na	na@gmail.com	-11.69
135	Jeffrey	HOUNGBEDJI	\N	\N	2025-11-02 08:39:34	2025-11-02 08:39:35	M	203	Sèmè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-11-08	3	3	https://res.cloudinary.com/marialain/image/upload/v1762072774/students_files/gfud0uqjc4orory3la53.jpg	\N	\N	\N	Na	na@gmail.com	-10.98
136	Ashnaf	BIOSYA	\N	\N	2025-11-02 08:43:06	2025-11-02 08:43:07	M	89	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-11-28	2	17	https://res.cloudinary.com/marialain/image/upload/v1762072986/students_files/qo6niagyg85pyzvo7skh.jpg	\N	\N	\N	Na	na@gmail.com	-9.93
112	Ornelia	GOULOLE	\N	\N	2025-11-01 21:08:25	2025-11-02 11:36:26	F	209070049368	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-09-13	3	4	https://res.cloudinary.com/marialain/image/upload/v1762031305/students_files/llfkvurwgxpvxqmqpok6.jpg	\N	\N	\N	NA	Na@gmail.com	-16.14
134	Kelvine	SAH	\N	\N	2025-11-02 08:36:03	2025-11-02 09:46:17	F	2140324067276	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-06-03	3	3	https://res.cloudinary.com/marialain/image/upload/v1762072563/students_files/rhvakvexygy5uxnt5kbj.jpg	\N	\N	\N	Na	na@gmail.com	-11.42
138	Maëlys	MONKOUN	\N	\N	2025-11-02 08:45:48	2025-11-02 08:45:49	F	205	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-04-08	3	3	https://res.cloudinary.com/marialain/image/upload/v1762073148/students_files/ynhbtq7wpngjfilzga3n.jpg	\N	\N	\N	Na	na@gmail.com	-10.57
139	Hospice	EBO	\N	\N	2025-11-02 08:46:25	2025-11-02 08:46:26	M	1150323130822	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-03-28	2	17	https://res.cloudinary.com/marialain/image/upload/v1762073185/students_files/cd52pg5zyvprqbwqjttt.jpg	\N	\N	\N	Na	na@gmail.com	-10.60
140	Junior	HOUNGUEVOU	\N	\N	2025-11-02 08:48:12	2025-11-02 08:48:12	M	206	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-04-20	3	3	https://res.cloudinary.com/marialain/image/upload/v1762073291/students_files/pem8ql2fzowbgrwfq0rj.jpg	\N	\N	\N	Na	na@gmail.com	-11.54
141	Sessimè Cécile Audrey	SAVI HOUDJREBO	\N	\N	2025-11-02 08:53:28	2025-11-02 08:53:29	F	90	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-05-15	2	17	https://res.cloudinary.com/marialain/image/upload/v1762073608/students_files/xg6r4kmuledbdx0tywdj.jpg	\N	\N	\N	Na	na@gmail.com	-10.47
142	Arielle	ATOLA	\N	\N	2025-11-02 08:53:45	2025-11-02 08:53:46	F	207	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-04-10	3	3	https://res.cloudinary.com/marialain/image/upload/v1762073625/students_files/wgm9bu9r6eerpakjp45e.jpg	\N	\N	\N	Na	na@gmail.com	-11.57
143	Méchac	ODJO	\N	\N	2025-11-02 08:57:03	2025-11-02 08:57:04	M	91	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-10-25	2	17	https://res.cloudinary.com/marialain/image/upload/v1762073823/students_files/dbtsfthjy4jtjoyiqtxm.jpg	\N	\N	\N	Na	na@gmail.com	-10.02
144	Premicelove	ALLOGAN	\N	\N	2025-11-02 08:58:57	2025-11-02 08:58:57	M	208	Parakou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-05-15	3	3	https://res.cloudinary.com/marialain/image/upload/v1762073936/students_files/pjmpnmomwunyunuqjfym.jpg	\N	\N	\N	Na	na@gmail.com	-10.47
145	Arik Kafui	HIDJO	\N	\N	2025-11-02 09:00:19	2025-11-02 09:00:19	M	92	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2025-09-25	2	17	https://res.cloudinary.com/marialain/image/upload/v1762074018/students_files/d4fshormzxpz0mbt9hch.jpg	\N	\N	\N	Na	na@gmail.com	-0.11
146	Sarah	SOUNNOUVOU	\N	\N	2025-11-02 09:00:50	2025-11-02 09:00:51	F	209	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-19	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074050/students_files/bgnkinfkgb5dgh24htcx.jpg	\N	\N	\N	Na	na@gmail.com	-10.87
147	Sammach	ABDOULAYE	\N	\N	2025-11-02 09:02:17	2025-11-02 09:02:18	M	210	Parakou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-02-22	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074137/students_files/qer8fhjrllywc5rldyw8.jpg	\N	\N	\N	Na	na@gmail.com	-10.69
148	Ordane Navonne	ZINSSOU	\N	\N	2025-11-02 09:03:02	2025-11-02 09:03:03	M	93	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-05-23	2	17	https://res.cloudinary.com/marialain/image/upload/v1762074182/students_files/jifwxtlsfh9vadn4adxl.jpg	\N	\N	\N	Na	na@gmail.com	-10.45
149	Précieux	CLOGBEDA	\N	\N	2025-11-02 09:04:05	2025-11-02 09:04:06	M	211	Sèmè kpodji	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-03-27	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074245/students_files/skrwfly7kdwktxh8vulo.jpg	\N	\N	\N	Na	na@gmail.com	-11.60
150	Melvina Coralie	MEHINTO	\N	\N	2025-11-02 09:05:18	2025-11-02 09:05:19	F	94	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-06-26	2	17	https://res.cloudinary.com/marialain/image/upload/v1762074318/students_files/ayknmqhyiblz796wzr2c.jpg	\N	\N	\N	Na	na@gmail.com	-10.35
151	Ruth	ADJAI	\N	\N	2025-11-02 09:05:33	2025-11-02 09:05:34	F	212	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-01-12	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074333/students_files/ygilz9unqwgrgorgfjpe.jpg	\N	\N	\N	Na	na@gmail.com	-13.81
152	Orphée	AHOMAGNON	\N	\N	2025-11-02 09:06:57	2025-11-02 09:06:57	F	213	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-08-10	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074416/students_files/mk5bzkr9t3eloummyrx4.jpg	\N	\N	\N	Na	na@gmail.com	-14.23
153	Cédric Dieu-Donné	ATCHIDI	\N	\N	2025-11-02 09:08:04	2025-11-02 09:08:04	M	95	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-10-09	2	17	https://res.cloudinary.com/marialain/image/upload/v1762074483/students_files/qwy4vrw6d7va0zv4ihu3.jpg	\N	\N	\N	Na	na@gmail.com	-9.07
154	Aryel	GUEZO GNAGBOLOU	\N	\N	2025-11-02 09:08:30	2025-11-02 09:08:30	M	214	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-31	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074509/students_files/caxm2qlpnmj1ipybxufb.jpg	\N	\N	\N	Na	na@gmail.com	-10.84
155	Prince	SODE	\N	\N	2025-11-02 09:10:13	2025-11-02 09:10:14	M	215	Porto-Novo	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-01-01	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074613/students_files/a3fu6v8oy0cozc4lnlda.jpg	\N	\N	\N	Na	na@gmail.com	-13.84
156	Ella	ZONON	\N	\N	2025-11-02 09:10:49	2025-11-02 09:10:49	F	96	Abomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-10-06	2	17	https://res.cloudinary.com/marialain/image/upload/v1762074648/students_files/kziaq5aclbuikex2p7pb.jpg	\N	\N	\N	Na	na@gmail.com	-11.08
157	Brunnel	ASSOGBA HEDOUGBEKOUN	\N	\N	2025-11-02 09:11:48	2025-11-02 09:11:49	M	216	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-24	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074708/students_files/ggg9dt4py7xhkepc4yk6.jpg	\N	\N	\N	Na	na@gmail.com	-10.86
158	Sirius	SOUNNOUHO	\N	\N	2025-11-02 09:14:55	2025-11-02 09:14:56	M	217	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-03-22	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074895/students_files/mpxxbaj1egp1canllfe5.jpg	\N	\N	\N	Na	na@gmail.com	-12.62
159	Steev	ATCHIDI	\N	\N	2025-11-02 09:16:30	2025-11-02 09:16:31	M	218	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-04-14	3	3	https://res.cloudinary.com/marialain/image/upload/v1762074990/students_files/jgavq9c7fq6aigbdecsp.jpg	\N	\N	\N	Na	na@gmail.com	-11.55
160	Osée	AHIHA	\N	\N	2025-11-02 09:19:02	2025-11-02 09:19:03	M	219	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-11-26	3	3	https://res.cloudinary.com/marialain/image/upload/v1762075142/students_files/fwwuwlyj00igukdecoci.jpg	\N	\N	\N	Na	na@gmail.com	-11.94
161	Limatou	AMOUSSOU	\N	\N	2025-11-02 09:20:18	2025-11-02 09:20:19	F	220	Bantè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-20	3	3	https://res.cloudinary.com/marialain/image/upload/v1762075218/students_files/sxejqaevkfwcxlk6lzbv.jpg	\N	\N	\N	Na	na@gmail.com	-10.87
162	Limatou	AMOUSSOU	\N	\N	2025-11-02 09:24:09	2025-11-02 09:24:10	F	221	Bantè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-20	3	3	https://res.cloudinary.com/marialain/image/upload/v1762075449/students_files/knels4xehqe13efs9bvi.jpg	\N	\N	\N	Na	na@gmail.com	-10.87
163	Césaire	KANGAN	\N	\N	2025-11-02 09:26:04	2025-11-02 09:26:04	M	222	Porto-Novo	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-08-26	3	3	https://res.cloudinary.com/marialain/image/upload/v1762075563/students_files/afpqjqoetr6jzwmxg3dq.jpg	\N	\N	\N	Na	na@gmail.com	-13.19
164	Djamiou	FAKEYE	\N	\N	2025-11-02 09:27:26	2025-11-02 09:27:27	M	223	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-03-23	3	3	https://res.cloudinary.com/marialain/image/upload/v1762075646/students_files/yi6g749wrljxd4pz991v.jpg	\N	\N	\N	Na	na@gmail.com	-11.61
165	Pruneil Camille	ASSOGBA HEDOUGBEKOUN	\N	\N	2025-11-02 09:27:39	2025-11-02 09:27:40	M	128	Godomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-07-18	2	15	https://res.cloudinary.com/marialain/image/upload/v1762075659/students_files/cecooa1ke8b4tlqynqmm.jpg	\N	\N	\N	NA	Na@gmail.com	-8.29
166	Séphora	ALLADATIN	\N	\N	2025-11-02 09:29:17	2025-11-02 09:29:18	F	124	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-06-03	3	3	https://res.cloudinary.com/marialain/image/upload/v1762075757/students_files/ttose3yipjufmtnwnjya.jpg	\N	\N	\N	Na	na@gmail.com	-11.42
167	Ketsia Aimée	AGUETE	\N	\N	2025-11-02 09:30:17	2025-11-02 09:30:18	F	129	Allada	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-03-23	2	15	https://res.cloudinary.com/marialain/image/upload/v1762075817/students_files/zemeqno7sz5wmmudq7ok.jpg	\N	\N	\N	NA	Na@gmail.com	-7.61
168	Patrick	ZONON	\N	\N	2025-11-02 09:32:11	2025-11-02 09:32:11	M	225	Abomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-03-14	3	5	https://res.cloudinary.com/marialain/image/upload/v1762075930/students_files/wfmsvizycc26qqbgggpz.jpg	\N	\N	\N	Na	na@gmail.com	-13.64
169	Olivier	HOUENOU	\N	\N	2025-11-02 09:33:36	2025-11-02 09:33:36	M	226	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-03-05	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076015/students_files/twbhnx91rnehqx8b9snt.jpg	\N	\N	\N	Na	na@gmail.com	-11.66
170	Yasmine	DZIMAKU	\N	\N	2025-11-02 09:33:41	2025-11-02 09:33:41	F	97	Akassato	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-03-18	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076020/students_files/hkoeveeykzbegckfyjdd.jpg	\N	\N	\N	Na	na@gmail.com	-13.63
171	Hervé	LOKOSSOU	\N	\N	2025-11-02 09:35:30	2025-11-02 09:35:30	M	227	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-05-30	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076129/students_files/t5wcfhhvnqfx4ttpajaz.jpg	\N	\N	\N	Na	na@gmail.com	-11.43
173	Manoël	GOUSSANOU	\N	\N	2025-11-02 09:36:50	2025-11-02 09:36:50	M	228	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-11-13	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076209/students_files/ztyl5q4vmze5sflcmez4.jpg	\N	\N	\N	Na	na@gmail.com	-11.97
174	Ethan Jérémy Nael	DENOU	\N	\N	2025-11-02 09:37:21	2025-11-02 09:37:22	M	1130323028352	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-12-30	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076241/students_files/cyjcdgekinsh7woen2bi.jpg	\N	\N	\N	Na	na@gmail.com	-11.84
175	Alex	KAKANAKOU	\N	\N	2025-11-02 09:38:54	2025-11-02 09:38:54	M	37602450704	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-02-17	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076333/students_files/f0sv62rzgdt6mj9ybhcq.jpg	\N	\N	\N	Na	na@gmail.com	-11.71
176	Sheila	AZA-GINANDJI	\N	\N	2025-11-02 09:39:03	2025-11-02 09:39:04	F	231	Atrokpocodji	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-08-27	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076343/students_files/ryk2okbkcgmrrvp8nuws.jpg	\N	\N	\N	NA	Na@gmail.com	-10.18
177	Jean Eudes	AHOUANDOGBO	\N	\N	2025-11-02 09:39:55	2025-11-02 09:39:56	M	98	Abomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-11-14	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076395/students_files/nrmdhptd6g6wqtrl2i38.jpg	\N	\N	\N	Na	na@gmail.com	-10.97
178	Michelle	DACLOUNON	\N	\N	2025-11-02 09:40:09	2025-11-02 09:40:10	F	229	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-08-12	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076409/students_files/raulwmnceehtlkxwve6k.jpg	\N	\N	\N	Na	na@gmail.com	-12.23
179	Elvire	SABI YERIMA	\N	\N	2025-11-02 09:41:14	2025-11-02 09:41:14	F	232	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-10-30	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076474/students_files/p6huq70crgf9npaadumn.jpg	\N	\N	\N	NA	Na@gmail.com	-8.01
180	Destinée	KLICO	\N	\N	2025-11-02 09:42:34	2025-11-02 09:42:35	F	230	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-05-08	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076554/students_files/ezvfoey5cghqpj2sxlum.jpg	\N	\N	\N	Na	na@gmail.com	-14.49
181	Ismaël	BATIMON ALI	\N	\N	2025-11-02 09:43:03	2025-11-02 09:43:03	M	99	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-10-22	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076583/students_files/pyx2cysvdpk7uz3i7sif.jpg	\N	\N	\N	Na	na@gmail.com	-11.03
182	Chancelle Dona Grâce	NOUNDJA	\N	\N	2025-11-02 09:43:17	2025-11-02 09:43:18	F	233	Cove	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-07-03	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076597/students_files/odbeacqym8posfzq3enp.jpg	\N	\N	\N	NA	Na@gmail.com	-8.34
183	Bienvenida Josiane	GOUSSANOU	\N	\N	2025-11-02 09:45:24	2025-11-02 09:45:24	F	234	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-06-19	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076723/students_files/dizeboba8fovg6svtk1m.jpg	\N	\N	\N	NA	Na@gmail.com	-8.37
184	Maelys Hosnia	DOVONOU	\N	\N	2025-11-02 09:47:37	2025-11-02 09:47:37	F	235	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-03-12	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076856/students_files/ctzdz6kxnti7snp5pec3.jpg	\N	\N	\N	NA	Na@gmail.com	-7.64
185	Keysenel	ZINSOUGA	\N	\N	2025-11-02 09:49:13	2025-11-02 09:49:13	M	202	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-09-15	3	5	https://res.cloudinary.com/marialain/image/upload/v1762076952/students_files/wv153p9l6856t25lbqso.jpg	\N	\N	\N	Na	na@gmail.com	-12.13
186	Wannreck	ALLOGAN	\N	\N	2025-11-02 09:49:58	2025-11-02 09:49:59	M	236	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-06-12	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076998/students_files/qqjjurkuy3fpwtczicyd.jpg	\N	\N	\N	NA	Na@gmail.com	-8.39
187	Gaël	TOSSOU	\N	\N	2025-11-02 09:52:01	2025-11-02 09:52:01	M	204	St Louis d'Akpassi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-12-16	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077120/students_files/n9qvos08hvuvi4oxjmfj.jpg	\N	\N	\N	Na	na@gmail.com	-13.88
188	Mohamed	BAH-L'IMAM	\N	\N	2025-11-02 09:52:14	2025-11-02 09:52:14	M	237	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-07-25	2	15	https://res.cloudinary.com/marialain/image/upload/v1762077133/students_files/gu0x5roscfgz1kj30hcd.jpg	\N	\N	\N	NA	Na@gmail.com	-9.28
189	Ruth	ATOLA	\N	\N	2025-11-02 09:55:33	2025-11-02 09:55:34	F	131	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-07-07	2	15	https://res.cloudinary.com/marialain/image/upload/v1762077333/students_files/piyqphpmdk7sftgtrd4y.jpg	\N	\N	\N	NA	Na@gmail.com	-8.32
190	Hafizath	ABDOULAYE	\N	\N	2025-11-02 09:56:39	2025-11-02 09:56:40	F	238	Parakou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-09-30	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077399/students_files/ofe2frv6yasjqnvxihmi.jpg	\N	\N	\N	Na	na@gmail.com	-13.09
191	Falonne	YEHOUME	\N	\N	2025-11-02 09:58:54	2025-11-02 09:58:54	F	239	Parakou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-09-13	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077533/students_files/wveprpdbnfr5dnaaoa9u.jpg	\N	\N	\N	Na	na@gmail.com	-12.14
172	Priscilla Yabo	ADONNAGBO	\N	\N	2025-11-02 09:36:17	2025-11-02 11:52:28	F	213032302265	Tankpê	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-06-09	2	15	https://res.cloudinary.com/marialain/image/upload/v1762076176/students_files/cymeobqs7bjnwuxmc6cj.jpg	\N	\N	\N	NA	Na@gmail.com	-12.40
192	Ange	AGBOGBE	\N	\N	2025-11-02 09:59:47	2025-11-02 09:59:47	M	132	Maria gleta	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-02-02	2	15	https://res.cloudinary.com/marialain/image/upload/v1762077586/students_files/gxywgptooylq5eszw5mb.jpg	\N	\N	\N	NA	Na@gmail.com	-8.75
193	Exaucé	GANKPAN	\N	\N	2025-11-02 10:01:13	2025-11-02 10:01:14	M	240	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-03-21	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077673/students_files/ngml6zamioqwxoc1lc7j.jpg	\N	\N	\N	Na	na@gmail.com	-11.62
194	Exaucé	AGBOGBE	\N	\N	2025-11-02 10:02:30	2025-11-02 10:02:30	F	133	CNHU	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-06	2	15	https://res.cloudinary.com/marialain/image/upload/v1762077749/students_files/ftjnmnedauktxyonxjmp.jpg	\N	\N	\N	NA	Na@gmail.com	-10.91
195	Charisma	COCOU	\N	\N	2025-11-02 10:02:47	2025-11-02 10:02:47	F	241	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-10-25	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077766/students_files/wrgwppskzejpqvr8hdde.jpg	\N	\N	\N	Na	na@gmail.com	-12.02
196	Emmanuel	ODJO	\N	\N	2025-11-02 10:04:07	2025-11-02 10:04:07	M	242	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-06-20	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077846/students_files/ww9xumqc53kxcydnbn8e.jpg	\N	\N	\N	Na	na@gmail.com	-12.37
197	Roland	FASSINOU	\N	\N	2025-11-02 10:05:22	2025-11-02 10:05:22	M	243	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-09-15	3	5	https://res.cloudinary.com/marialain/image/upload/v1762077921/students_files/xuqmvud7qmvbaqoq8pck.jpg	\N	\N	\N	Na	na@gmail.com	-12.13
198	Isabelle	BOCO	\N	\N	2025-11-02 10:05:22	2025-11-02 10:05:23	F	134	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-02-22	2	15	https://res.cloudinary.com/marialain/image/upload/v1762077922/students_files/hv6lbsfypthtm0g1gmnl.jpg	\N	\N	\N	NA	Na@gmail.com	-8.69
199	Wakiratou	ADAMOU	\N	\N	2025-11-02 10:06:59	2025-11-02 10:06:59	F	244	Djougou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-04-26	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078018/students_files/vdpefeit5rgvecuzdzmj.jpg	\N	\N	\N	Na	na@gmail.com	-14.52
200	Grâce Divine	KPOZOUNME	\N	\N	2025-11-02 10:07:15	2025-11-02 10:07:16	F	135	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-08-04	2	15	https://res.cloudinary.com/marialain/image/upload/v1762078035/students_files/it5xxtiougne1deggvle.jpg	\N	\N	\N	NA	Na@gmail.com	-9.25
201	Bruno	DOSSOU	\N	\N	2025-11-02 10:09:32	2025-11-02 10:09:33	M	136	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-10-05	2	15	https://res.cloudinary.com/marialain/image/upload/v1762078172/students_files/lmaumckwb6tijuxzeuls.jpg	\N	\N	\N	NA	Na@gmail.com	-7.08
202	Christ-Love	AVOCE KOUNOUDJI	\N	\N	2025-11-02 10:10:35	2025-11-02 10:10:35	F	245	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-07-10	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078234/students_files/kffv2d90u3u7ivr1z7dr.jpg	\N	\N	\N	Na	na@gmail.com	-15.32
203	Merlin Pierre,	MEHINTO	\N	\N	2025-11-02 10:12:13	2025-11-02 10:12:14	M	137	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-06-26	2	15	https://res.cloudinary.com/marialain/image/upload/v1762078333/students_files/gdaonsrpaksvdguyh5rn.jpg	\N	\N	\N	NA	Na@gmail.com	-9.35
204	Sica	VODOUNOU	\N	\N	2025-11-02 10:13:05	2025-11-02 10:13:06	F	246	Cococodji	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-09-08	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078385/students_files/r7ste9pbod1mx9concow.jpg	\N	\N	\N	Na	na@gmail.com	-12.15
205	Omaël	DEGAN	\N	\N	2025-11-02 10:14:54	2025-11-02 10:14:55	M	247	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-08-20	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078494/students_files/t2hb9xd6kg5xzjpygigf.jpg	\N	\N	\N	Na	na@gmail.com	-13.20
206	Merveille	ADOUSSINGNADE	\N	\N	2025-11-02 10:15:59	2025-11-02 10:16:00	F	138	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-06-25	2	15	https://res.cloudinary.com/marialain/image/upload/v1762078559/students_files/h46msu0x6gz0fftebcxc.jpg	\N	\N	\N	NA	Na@gmail.com	-8.36
207	Noha	DAH-MOROU	\N	\N	2025-11-02 10:17:59	2025-11-02 10:18:00	M	248	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-10-21	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078678/students_files/edhjsjgqvap4oaj47iix.jpg	\N	\N	\N	Na	na@gmail.com	-11.03
208	Othniel	AVODAGBE	\N	\N	2025-11-02 10:18:42	2025-11-02 10:18:43	M	139	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-01-30	2	15	https://res.cloudinary.com/marialain/image/upload/v1762078721/students_files/ujlnhjwrb3dbxhi7egvu.jpg	\N	\N	\N	NA	Na@gmail.com	-7.76
209	Metonnou	METONNOU	\N	\N	2025-11-02 10:20:22	2025-11-02 10:20:23	M	249	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-04-06	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078822/students_files/tigt7svkffcpfu4vypoe.jpg	\N	\N	\N	Na	na@gmail.com	-12.58
210	Carole	ANAGOSSI	\N	\N	2025-11-02 10:21:39	2025-11-02 10:21:40	F	250	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-01-23	3	5	https://res.cloudinary.com/marialain/image/upload/v1762078899/students_files/z2bixz9vnykln3bmszce.jpg	\N	\N	\N	Na	na@gmail.com	-12.78
211	Grâce	KPONON	\N	\N	2025-11-02 10:23:52	2025-11-02 10:23:52	F	251	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-07-02	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079031/students_files/xi3ayzdq98vn87skdj3y.jpg	\N	\N	\N	Na	na@gmail.com	-13.34
212	Hermann	KINDE	\N	\N	2025-11-02 10:25:25	2025-11-02 10:25:26	M	252	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-09-26	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079125/students_files/xtumyw9ewkvzzs5b3txa.jpg	\N	\N	\N	Na	na@gmail.com	-14.10
213	Mohamed	AMOUSSOU	\N	\N	2025-11-02 10:27:20	2025-11-02 10:27:21	M	253	Issati	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-02-28	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079240/students_files/llriggq3ke73ebrbvd5r.jpg	\N	\N	\N	Na	na@gmail.com	-15.68
214	Carlos	ADADJI	\N	\N	2025-11-02 10:28:53	2025-11-02 10:28:54	M	254	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-06-17	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079333/students_files/iz7bwtxl0a9bym1ij0fc.jpg	\N	\N	\N	Na	na@gmail.com	-13.38
215	Adalric	DADEHOU	\N	\N	2025-11-02 10:30:27	2025-11-02 10:30:27	M	255	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-06-06	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079426/students_files/xqrwhxvt9zgvxybbxp0g.jpg	\N	\N	\N	Na	na@gmail.com	-13.41
216	Charles	GODONOU	\N	\N	2025-11-02 10:31:52	2025-11-02 10:31:53	M	140	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-11-24	2	15	https://res.cloudinary.com/marialain/image/upload/v1762079512/students_files/cbf5d7twpwe8dbdgyhuk.jpg	\N	\N	\N	NA	Na@gmail.com	-7.94
217	Imane	BAH-AGBA	\N	\N	2025-11-02 10:32:06	2025-11-02 10:32:06	F	256	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-11-05	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079525/students_files/qdxcidjw8blvqaoqcjvf.jpg	\N	\N	\N	Na	na@gmail.com	-12.99
218	Lionel-Walé	SODE	\N	\N	2025-11-02 10:33:44	2025-11-02 10:33:45	M	141	Porto Novo	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-05-15	2	15	https://res.cloudinary.com/marialain/image/upload/v1762079624/students_files/unlorx4dk74hydqqtx6c.jpg	\N	\N	\N	NA	Na@gmail.com	-10.47
219	Calfridath	TOSSOUKPE	\N	\N	2025-11-02 10:34:13	2025-11-02 10:34:13	F	257	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-10-04	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079652/students_files/sq2tvuawl5lmk9h7afm6.jpg	\N	\N	\N	Na	na@gmail.com	-14.08
220	Horacio	VODONOU	\N	\N	2025-11-02 10:35:33	2025-11-02 10:35:33	M	142	Cococodji	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-05-08	2	15	https://res.cloudinary.com/marialain/image/upload/v1762079732/students_files/g0i0fyeenuojzzypn7ox.jpg	\N	\N	\N	N	Na@gmail.com	-8.49
221	Majorelle	GOMENOU	\N	\N	2025-11-02 10:36:12	2025-11-02 10:36:12	F	258	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-12-22	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079771/students_files/y1q38ozmghcmsy9iqeos.jpg	\N	\N	\N	Na	na@gmail.com	-12.86
222	Donald	SABI HOUDJREBO	\N	\N	2025-11-02 10:37:39	2025-11-02 10:37:39	M	143	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-01-24	2	15	https://res.cloudinary.com/marialain/image/upload/v1762079858/students_files/emsrf84b8udqybuqgdcw.jpg	\N	\N	\N	NA	Na@gmail.com	-7.77
223	Géraldine	ADADJI	\N	\N	2025-11-02 10:37:53	2025-11-02 10:37:53	F	259	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-10-03	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079872/students_files/skac2uzdk9rt5pfzpxub.jpg	\N	\N	\N	Na	na@gmail.com	-14.08
224	Leslie	KOHLA	\N	\N	2025-11-02 10:39:48	2025-11-02 10:39:49	F	260	Ekpè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-12-14	3	6	https://res.cloudinary.com/marialain/image/upload/v1762079988/students_files/sbjf90rro5xwzxy0zuhj.jpg	\N	\N	\N	Na	na@gmail.com	-14.89
225	Daniel Fenou	ASSOGBA	\N	\N	2025-11-02 10:39:50	2025-11-02 10:39:50	M	144	Ze	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-03-18	2	15	https://res.cloudinary.com/marialain/image/upload/v1762079989/students_files/estbugjkauky20hp1ec0.jpg	\N	\N	\N	NA	Na@gmail.com	-8.63
226	Daniella	AGBALI	\N	\N	2025-11-02 10:41:31	2025-11-02 10:41:31	F	261	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-05-20	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080090/students_files/ej8dixhvcyfqw19zbwqy.jpg	\N	\N	\N	Na	na@gmail.com	-12.46
227	Marie-Nelckael	SEHA	\N	\N	2025-11-02 10:42:24	2025-11-02 10:42:25	F	145	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-07-09	2	15	https://res.cloudinary.com/marialain/image/upload/v1762080144/students_files/hp0vi7wz3bt2crsxsoxi.jpg	\N	\N	\N	NA	Na@gmail.com	-8.32
228	Elie	CLOGBEDJA	\N	\N	2025-11-02 10:43:56	2025-11-02 10:43:56	M	262	Sèmè kpodji	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-08-03	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080235/students_files/gk3fuelniq3fx8a6m5ls.jpg	\N	\N	\N	Na	na@gmail.com	-16.25
229	Rayane	ZOUNGNI	\N	\N	2025-11-02 10:45:58	2025-11-02 10:45:59	M	146	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-08-31	2	15	https://res.cloudinary.com/marialain/image/upload/v1762080358/students_files/msv0lw4q6cfo7m7dnhbx.jpg	\N	\N	\N	NA	Na@gmail.com	-7.17
230	Sosthène	VODOUNOU	\N	\N	2025-11-02 10:46:03	2025-11-02 10:46:04	M	263	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-11-27	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080363/students_files/iswxnhzc65gbcarp4ulw.jpg	\N	\N	\N	Na	na@gmail.com	-14.93
231	Bricette	KANGAN	\N	\N	2025-11-02 10:48:56	2025-11-02 10:48:57	F	264	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-02-27	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080536/students_files/knbcxwz0fgdtlzgoahs7.jpg	\N	\N	\N	Na	na@gmail.com	-15.68
232	Rayan	HOUANGNI	\N	\N	2025-11-02 10:50:36	2025-11-02 10:50:36	M	265	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-04-28	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080635/students_files/myw3k6osbnmuss1hrugm.jpg	\N	\N	\N	Na	na@gmail.com	-12.52
233	Wonderful-stone	AFAFA	\N	\N	2025-11-02 10:52:06	2025-11-02 10:52:06	F	147	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-05-31	2	15	https://res.cloudinary.com/marialain/image/upload/v1762080725/students_files/adanq7xwbna5zu8tpyei.jpg	\N	\N	\N	NA	Na@gmail.com	-8.43
234	Nadine	BADE	\N	\N	2025-11-02 10:52:16	2025-11-02 10:52:17	F	266	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-11-08	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080736/students_files/ur091kpvysijm19i7voc.jpg	\N	\N	\N	Na	na@gmail.com	-12.98
235	Bérékia	AYINON	\N	\N	2025-11-02 10:54:06	2025-11-02 10:54:07	F	267	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-07-22	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080846/students_files/hyru6dfni9f2gpqs34ei.jpg	\N	\N	\N	Na	na@gmail.com	-13.28
236	Abideele	AYINON	\N	\N	2025-11-02 10:54:51	2025-11-02 10:54:52	F	148	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-08-18	2	15	https://res.cloudinary.com/marialain/image/upload/v1762080890/students_files/kejixthky0cndcynm7yj.jpg	\N	\N	\N	NA	Na@gmail.com	-7.21
237	Mouzâkir	BACHABI ALIDOU	\N	\N	2025-11-02 10:56:12	2025-11-02 10:56:13	M	268	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-01-15	3	6	https://res.cloudinary.com/marialain/image/upload/v1762080972/students_files/mshwvijkwtytpbilitgi.jpg	\N	\N	\N	Na	na@gmail.com	-12.80
238	Maelys	AHYEE	\N	\N	2025-11-02 10:56:44	2025-11-02 10:56:45	F	149	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-03-27	2	15	https://res.cloudinary.com/marialain/image/upload/v1762081003/students_files/rkykcwrqfurogfzbbdom.jpg	\N	\N	\N	NA	Na@gmail.com	-7.60
239	Carine	ADADJI	\N	\N	2025-11-02 10:58:59	2025-11-02 10:59:00	F	269	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-06-17	3	6	https://res.cloudinary.com/marialain/image/upload/v1762081139/students_files/dlysgcqvuqkddhrv6mxc.jpg	\N	\N	\N	Na	na@gmail.com	-13.38
240	William	FESSINOU	\N	\N	2025-11-02 11:00:09	2025-11-02 11:00:09	M	150	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-03-13	2	15	https://res.cloudinary.com/marialain/image/upload/v1762081208/students_files/dpqnqjgknq7srds9iul0.jpg	\N	\N	\N	NA	Na@gmail.com	-7.64
241	Clotilde	HOUEMABE	\N	\N	2025-11-02 11:01:38	2025-11-02 11:01:39	F	270	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-03-22	3	6	https://res.cloudinary.com/marialain/image/upload/v1762081297/students_files/r0tqdqux7bkud2irrdrr.jpg	\N	\N	\N	Na	na@gmail.com	-13.62
242	Pascaline	ABAGLI	\N	\N	2025-11-02 11:03:52	2025-11-02 11:03:53	F	151	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2018-04-29	2	15	https://res.cloudinary.com/marialain/image/upload/v1762081432/students_files/flga14or6yv57x6vzqo4.jpg	\N	\N	\N	NA	Na@gmail.com	-7.51
243	Gracia	EHO	\N	\N	2025-11-02 11:04:07	2025-11-02 11:04:08	F	207120252230	Ekpe	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2007-10-26	3	6	https://res.cloudinary.com/marialain/image/upload/v1762081447/students_files/kigpioffk7c8d2qobcg9.jpg	\N	\N	\N	Na	na@gmail.com	-18.02
244	Isaac	BOCO	\N	\N	2025-11-02 11:05:48	2025-11-02 11:05:48	M	271	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-10-03	3	6	https://res.cloudinary.com/marialain/image/upload/v1762081547/students_files/t2ad2uj2tebqweljodok.jpg	\N	\N	\N	Na	na@gmail.com	-14.08
245	Calvin	ALLADATIN	\N	\N	2025-11-02 11:06:17	2025-11-02 11:06:18	M	152	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-11-23	2	15	https://res.cloudinary.com/marialain/image/upload/v1762081577/students_files/tdspkhr8yysjilndtu1g.jpg	\N	\N	\N	NA	Na@gmail.com	-7.94
246	Wera	SABI	\N	\N	2025-11-02 11:08:02	2025-11-02 11:08:03	M	272	Natitingou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-09-04	3	6	https://res.cloudinary.com/marialain/image/upload/v1762081682/students_files/tg8vypirixgep9jdeavv.jpg	\N	\N	\N	Na	na@gmail.com	-13.16
247	Jorden	DJOSSOU	\N	\N	2025-11-02 11:08:31	2025-11-02 11:08:32	M	153	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-06-01	2	15	https://res.cloudinary.com/marialain/image/upload/v1762081711/students_files/uxwvfemv1v8r1yk03adk.jpg	\N	\N	\N	NA	Na@gmail.com	-10.42
248	Robert	AZANDOSSESSI	\N	\N	2025-11-02 11:10:04	2025-11-02 11:10:04	M	273	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-04-30	3	6	https://res.cloudinary.com/marialain/image/upload/v1762081803/students_files/ahtgltcq4pnzj9vxfjsx.jpg	\N	\N	\N	Na	na@gmail.com	-13.51
249	Sadia	BATIMON ALI	\N	\N	2025-11-02 11:23:49	2025-11-02 11:23:50	F	275	Gbegamey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-05-19	2	15	https://res.cloudinary.com/marialain/image/upload/v1762082627/students_files/mzb3fvskugyxxeymhsk9.jpg	\N	\N	\N	Na	na@gmail.com	-13.46
250	Divine	ADANDE	\N	\N	2025-11-02 11:25:03	2025-11-02 11:25:04	F	274	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-04-07	3	7	https://res.cloudinary.com/marialain/image/upload/v1762082703/students_files/hjhz2ijehoqys5kmt2rd.jpg	\N	\N	\N	Na	na@gmail.com	-13.57
251	Emmanuella	BADE	\N	\N	2025-11-02 11:27:09	2025-11-02 11:27:09	F	276	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-12-28	3	7	https://res.cloudinary.com/marialain/image/upload/v1762082828/students_files/qvhloyt19dhfkbrqxb1j.jpg	\N	\N	\N	Na	na@gmail.com	-13.85
252	Yanëlle	EDAYE	\N	\N	2025-11-02 11:34:36	2025-11-02 11:34:36	F	277	Godomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-06-23	3	7	https://res.cloudinary.com/marialain/image/upload/v1762083275/students_files/xpcavjqgn4tevhzlacdg.jpg	\N	\N	\N	Na	na@gmail.com	-13.36
253	Chritiana	AZONDEKOU	\N	\N	2025-11-02 11:38:19	2025-11-02 11:38:20	F	118	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-07-02	3	7	https://res.cloudinary.com/marialain/image/upload/v1762083499/students_files/xj1yclyunxymuuf5plzk.jpg	\N	\N	\N	Na	na@gmail.com	-13.34
254	Marjonelle	GNAHA	\N	\N	2025-11-02 11:41:28	2025-11-02 11:41:29	F	126	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-06-17	3	7	https://res.cloudinary.com/marialain/image/upload/v1762083688/students_files/jonx3brapragf84hg7eq.jpg	\N	\N	\N	Na	na@gmail.com	-13.38
255	Charlmagne	SENANKPON	\N	\N	2025-11-02 11:47:13	2025-11-02 11:47:14	M	110120315363	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-05-09	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084033/students_files/vh6lmbbqryxxbaew1uzc.jpg	\N	\N	\N	Na	na@gmail.com	-15.49
256	Mariella	ASSOGBA	\N	\N	2025-11-02 11:49:21	2025-11-02 11:49:22	F	278	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-04-13	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084161/students_files/sggzzqsw7ppb4cm3ihf5.jpg	\N	\N	\N	Na	na@gmail.com	-15.56
257	Frey	DOUKPO	\N	\N	2025-11-02 11:51:33	2025-11-02 11:51:34	M	279	Allada	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2009-11-13	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084293/students_files/ok0uk7eqmdtgsygfxtms.jpg	\N	\N	\N	Na	na@gmail.com	-15.97
258	Ange	HOUNGUEVOU	\N	\N	2025-11-02 11:55:09	2025-11-02 11:55:09	M	130	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-02-03	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084508/students_files/otaymsbef2vhfd7cwwbt.jpg	\N	\N	\N	Na	na@gmail.com	-14.75
259	Marie Anne	LOKONON	\N	\N	2025-11-02 11:57:34	2025-11-02 11:57:35	F	280	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-05-28	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084654/students_files/crtujdcqbropqigjjd48.jpg	\N	\N	\N	Na	na@gmail.com	-14.43
260	Ezéchiel	AGBALI	\N	\N	2025-11-02 11:59:27	2025-11-02 11:59:28	M	281	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-02-10	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084767/students_files/vd4kbpni9helto0emox8.jpg	\N	\N	\N	Na	na@gmail.com	-14.73
261	Fierté	DETONDJI	\N	\N	2025-11-02 12:03:12	2025-11-02 12:03:13	F	282	Abomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-05-21	3	7	https://res.cloudinary.com/marialain/image/upload/v1762084990/students_files/nhchwvan5mr9vp42opft.jpg	\N	\N	\N	Na	na@gmail.com	-13.45
262	Clément	GNADEKPA	\N	\N	2025-11-02 12:07:45	2025-11-02 12:07:46	M	283	Comé	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-03-22	3	7	https://res.cloudinary.com/marialain/image/upload/v1762085265/students_files/mw9psph3o6aoe0azquay.jpg	\N	\N	\N	Na	na@gmail.com	-15.62
263	Stéphanas	VODOUNSI	\N	\N	2025-11-02 12:10:23	2025-11-02 12:10:24	M	284	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2008-04-14	3	7	https://res.cloudinary.com/marialain/image/upload/v1762085423/students_files/ser6jutxkixvbfywig5e.jpg	\N	\N	\N	Na	na@gmail.com	-17.55
264	Miracle	GOUSSANOU	\N	\N	2025-11-02 12:13:04	2025-11-02 12:13:04	M	285	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-07-01	3	7	https://res.cloudinary.com/marialain/image/upload/v1762085583/students_files/bpll9pp6ram5zvk9yupj.jpg	\N	\N	\N	Na	na@gmail.com	-14.34
265	Sylvain	GNANVI	\N	\N	2025-11-02 12:15:17	2025-11-02 12:15:17	M	286	Savalou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2006-11-05	3	7	https://res.cloudinary.com/marialain/image/upload/v1762085716/students_files/otbiynpflwtjaelvqpvy.jpg	\N	\N	\N	Na	na@gmail.com	-18.99
266	Faouziath	BAH LIMAM	\N	\N	2025-11-02 12:18:12	2025-11-02 12:18:13	F	287	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-11-29	3	7	https://res.cloudinary.com/marialain/image/upload/v1762085892/students_files/vwlb2t0oa7newou4iuyq.jpg	\N	\N	\N	Na	na@gmail.com	-14.93
267	Hermione	AHOUANMAGNAGAHOU	\N	\N	2025-11-02 12:20:18	2025-11-02 12:20:18	F	288	Abomey	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-05-15	3	7	https://res.cloudinary.com/marialain/image/upload/v1762086017/students_files/qhms5bmebfjxrhb6tz3v.jpg	\N	\N	\N	Na	na@gmail.com	-15.47
268	Faouziath	BAH L'IMAM MOUSSA	\N	\N	2025-11-02 12:22:56	2025-11-02 12:22:56	F	289	Djougou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-03-05	3	7	https://res.cloudinary.com/marialain/image/upload/v1762086175/students_files/hzcrapovyjr6hx6922fo.jpg	\N	\N	\N	Na	na@gmail.com	-15.66
269	Samir	MOUTAIROU	\N	\N	2025-11-02 12:25:05	2025-11-02 12:25:05	M	290	Porto-Novo	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-08-04	3	7	https://res.cloudinary.com/marialain/image/upload/v1762086304/students_files/is2j8fkmdz2kqyl3ff7f.jpg	\N	\N	\N	Na	na@gmail.com	-13.25
270	Alex	DIDAGBE	\N	\N	2025-11-02 12:26:48	2025-11-02 12:26:48	M	291	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2011-04-22	3	7	https://res.cloudinary.com/marialain/image/upload/v1762086407/students_files/gshlkitvye54us3jgdr2.jpg	\N	\N	\N	Na	na@gmail.com	-14.53
271	Vital	AHAMIDE	\N	\N	2025-11-02 12:28:37	2025-11-02 12:28:38	M	292	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2012-03-12	3	7	https://res.cloudinary.com/marialain/image/upload/v1762086517/students_files/pthy1d5bdprjkrqeygyz.jpg	\N	\N	\N	Na	na@gmail.com	-13.65
272	Immaculée	KOUNDE	\N	\N	2025-11-02 12:31:01	2025-11-02 12:31:01	F	294	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2010-08-27	3	7	https://res.cloudinary.com/marialain/image/upload/v1762086660/students_files/bhowcypb5nlthabsopd6.jpg	\N	\N	\N	Na	na@gmail.com	-15.18
273	Gilchrist Espoir	ZEHOUNKPE	\N	\N	2025-11-02 12:38:37	2025-11-02 12:38:37	M	154	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-07-12	2	16	https://res.cloudinary.com/marialain/image/upload/v1762087116/students_files/vv7dnjxtwedvdiatntk0.jpg	\N	\N	\N	NA	Na@gmail.com	-9.31
274	Brandon Hernès	SOARES	\N	\N	2025-11-02 12:40:37	2025-11-02 12:40:38	M	155	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-05-25	2	16	https://res.cloudinary.com/marialain/image/upload/v1762087237/students_files/dlrw2ipry0tybiq8h5fj.jpg	\N	\N	\N	NA	Na@gmail.com	-10.44
275	Marie Sagesse	HOUSSOU	\N	\N	2025-11-02 12:42:30	2025-11-02 12:42:31	F	156	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-01-30	2	16	https://res.cloudinary.com/marialain/image/upload/v1762087350/students_files/bdmp7n2kcsh9xhhibf9p.jpg	\N	\N	\N	NA	Na@gmail.com	-8.76
276	Arrinesse Mahoutin	AMOUME-GANGNIHESSOU	\N	\N	2025-11-02 12:50:34	2025-11-02 12:50:34	F	157	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-12-17	2	16	https://res.cloudinary.com/marialain/image/upload/v1762087833/students_files/pgpwxikjz8sbnrjd0x60.jpg	\N	\N	\N	NA	Na@gmail.com	-8.88
277	Bénédicte Aurore	ANATO	\N	\N	2025-11-02 12:59:02	2025-11-02 12:59:02	F	158	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-04-04	2	16	https://res.cloudinary.com/marialain/image/upload/v1762088341/students_files/hjuulw1qxb5enjnbvwew.jpg	\N	\N	\N	NA	Na@gmail.com	-8.58
278	Yabo Esperencia	SOUNNOUVOU	\N	\N	2025-11-02 13:05:11	2025-11-02 13:05:11	F	160	Abomey calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-12-20	2	16	https://res.cloudinary.com/marialain/image/upload/v1762088710/students_files/zxzojcagykh8xqvinsa7.jpg	\N	\N	\N	NA	Na@gmail.com	-8.87
279	Lucky	OTCHO	\N	\N	2025-11-02 17:20:50	2025-11-02 17:20:51	M	161	Tankpè	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-12-06	2	16	https://res.cloudinary.com/marialain/image/upload/v1762104050/students_files/sxnapp0uxfz7nkdy2hk4.jpg	\N	\N	\N	NA	na@gmail.com	-8.91
280	Maria Anaïs	AHYEE	\N	\N	2025-11-02 17:23:10	2025-11-02 17:23:11	F	162	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-11-11	2	16	https://res.cloudinary.com/marialain/image/upload/v1762104190/students_files/eij0xybz7saagparozj3.jpg	\N	\N	\N	NA	na@gmail.com	-9.98
281	Bignon Jean	BADE	\N	\N	2025-11-02 17:28:10	2025-11-02 17:28:11	M	163	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-01-06	2	16	https://res.cloudinary.com/marialain/image/upload/v1762104490/students_files/xmt4jenqkgy7zth0hger.jpg	\N	\N	\N	NA	na@gmail.com	-8.82
282	Rafaela Kelya	HOUANGNI	\N	\N	2025-11-02 17:30:23	2025-11-02 17:30:23	F	164	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-11-28	2	16	https://res.cloudinary.com/marialain/image/upload/v1762104622/students_files/seglvw9iir2hyje4uplz.jpg	\N	\N	\N	NA	na@gmail.com	-8.93
283	Déo Gracias Solveig	HOUNGBEDJI	\N	\N	2025-11-02 17:32:35	2025-11-02 17:32:36	M	165	C	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-05-10	2	16	https://res.cloudinary.com/marialain/image/upload/v1762104755/students_files/rk2bkk26pqwxfqjawodk.jpg	\N	\N	\N	NA	na@gmail.com	-8.48
284	Bill Roosevelt	GUEZO GNAGBOLOU	\N	\N	2025-11-02 17:40:30	2025-11-02 17:40:31	M	166	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-09-19	2	16	https://res.cloudinary.com/marialain/image/upload/v1762105230/students_files/ktqecelell8kid0vtn1j.jpg	\N	\N	\N	NA	na@gmail.com	-9.12
285	Conceptia Joanica	EDAYE	\N	\N	2025-11-02 17:44:08	2025-11-02 17:44:09	F	167	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-04-18	2	16	https://res.cloudinary.com/marialain/image/upload/v1762105448/students_files/npanzvlqzwj1zoz5oefw.jpg	\N	\N	\N	NA	na@gmail.com	-8.54
286	Exaucé Ronel	SOHE	\N	\N	2025-11-02 17:54:23	2025-11-02 17:54:23	M	168	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2017-01-11	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106062/students_files/somjlhgmkgjsmeae6pmt.jpg	\N	\N	\N	NA	na@gmail.com	-8.81
287	Segnon Kenneth	KINNOUMI	\N	\N	2025-11-02 17:56:19	2025-11-02 17:56:19	M	169	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-11-30	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106178/students_files/nof0kjofdc96tti1ipbb.jpg	\N	\N	\N	NA	na@gmail.com	-10.93
288	Jesuella	AWEKPON	\N	\N	2025-11-02 17:58:32	2025-11-02 17:58:33	F	2160323831542	Oui	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-04-28	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106312/students_files/oidvxpa6bkburwiksa9f.jpg	\N	\N	\N	NA	na@gmail.com	-9.52
289	Adnette	AGOSSOUKPE	\N	\N	2025-11-02 18:01:50	2025-11-02 18:01:50	F	170	Savalou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-11-03	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106509/students_files/byxcipfh4sjokgvgf7ur.jpg	\N	\N	\N	NA	na@gmail.com	-9.00
290	Privilège	FANOU	\N	\N	2025-11-02 18:03:42	2025-11-02 18:03:43	M	171	Togoudo	0	0.00	f	f	\N	t	0.00	0.00	1	2014-07-20	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106622/students_files/yyas12dojdumzrsqksny.jpg	\N	\N	\N	NA	na@gmail.com	-11.29
291	Auxnelle	ADIDO	\N	\N	2025-11-02 18:05:40	2025-11-02 18:05:41	F	172	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-05-16	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106739/students_files/g3n2wnmgb0h8uqq0dabx.jpg	\N	\N	\N	NA	na@gmail.com	-9.47
292	Fanny	NOUGBODE	\N	\N	2025-11-02 18:07:59	2025-11-02 18:07:59	F	173	Abomey Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-08-19	2	16	https://res.cloudinary.com/marialain/image/upload/v1762106878/students_files/mzzohxg8mpdhkeh2b4h5.jpg	\N	\N	\N	NA	na@gmail.com	-9.21
293	Anselme Yohan	KAGOUA	\N	\N	2025-11-02 18:20:43	2025-11-02 18:20:44	M	174	Calavi	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2013-04-21	2	16	https://res.cloudinary.com/marialain/image/upload/v1762107643/students_files/ukaazfacl2insrszw4cy.jpg	\N	\N	\N	NA	na@gmail.com	-12.54
294	David Daryl	KOUTY	\N	\N	2025-11-02 18:22:49	2025-11-02 18:22:49	M	175	Malanville	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2014-12-01	2	16	https://res.cloudinary.com/marialain/image/upload/v1762107768/students_files/qsnfbpkqv4ec08ihgp0s.jpg	\N	\N	\N	NA	na@gmail.com	-10.92
295	Jordan	DJOSSOU	\N	\N	2025-11-02 18:24:15	2025-11-02 18:24:15	M	176	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2015-06-01	2	16	https://res.cloudinary.com/marialain/image/upload/v1762107854/students_files/y1b8e5insxv9hbfil3qk.jpg	\N	\N	\N	NA	na@gmail.com	-10.42
296	Schania	ADANDE	\N	\N	2025-11-02 18:26:00	2025-11-02 18:26:01	F	177	Cotonou	01 90 00 00 00	0.00	f	f	\N	t	0.00	0.00	1	2016-03-31	2	16	https://res.cloudinary.com/marialain/image/upload/v1762107960/students_files/dpfzm9jdnlf9u1go7quk.jpg	\N	\N	\N	NA	na@gmail.com	-9.59
297	Ifè Mauraine Ahouèfa	CHINCOUN	\N	\N	2025-11-18 15:43:49	2025-11-18 15:52:29	F	1200	Cotonou	0195151984	0.00	f	f	\N	t	0.00	\N	1	2010-11-18	3	23	https://res.cloudinary.com/marialain/image/upload/v1763480628/students_files/stua3kqvzmdemocs50wd.png	\N	\N	\N	CHINCOUN Victorin	na@gmail.com	-15.00
299	Ashey Maéva	ADOUNGBE	\N	\N	2025-11-18 15:49:39	2025-11-18 16:05:38	F	1255	Louisiane	0195794041	0.00	f	f	\N	t	0.00	\N	1	2010-02-22	3	23	https://res.cloudinary.com/marialain/image/upload/v1763480979/students_files/vutiwq8feupfynwue6qx.png	\N	\N	\N	ADOUNGBE Samson	na@gmail.com	-15.74
300	Houefa Blanche Eunice	KOHONOU	\N	\N	2025-11-18 15:51:16	2025-11-18 16:06:02	F	12569	Cotonou	0197333444	0.00	f	f	\N	t	0.00	\N	1	2009-12-31	3	23	https://res.cloudinary.com/marialain/image/upload/v1763481075/students_files/y3r8zvdkt7xfjdbsngph.png	\N	\N	\N	KOHONOU Janvier	na@gmail.com	-15.88
301	Olagninka Grâcia Mervelle	AYEDOUN	\N	\N	2025-11-18 15:53:19	2025-11-18 16:06:46	F	1250	Abomey-Calavi	0195648882	0.00	f	f	\N	t	0.00	\N	1	2008-02-02	3	23	https://res.cloudinary.com/marialain/image/upload/v1763481198/students_files/iexafggqrccsm8uxeakq.png	\N	\N	\N	AYEDOUN Akpaki Esaïe	na@gmail.com	-17.79
298	Andy Naasson Vidékon	TCHINKOUN	\N	\N	2025-11-18 15:47:48	2025-11-18 16:02:42	M	1256	Cotonou	0195051724	0.00	f	f	\N	t	0.00	\N	1	2010-01-25	3	23	https://res.cloudinary.com/marialain/image/upload/v1763480867/students_files/sbyimey8gp0svxuksr08.png	\N	\N	\N	TCHINKOUN Arnaud Brice	na@gmail.com	-15.82
302	Phil-Terry Dègnon Adéola	FAGNIBO	\N	\N	2025-11-18 15:58:23	2025-11-18 16:07:00	M	12359	Cotonou	0168640000	0.00	f	f	\N	t	0.00	\N	1	2009-07-19	3	23	https://res.cloudinary.com/marialain/image/upload/v1763481502/students_files/jfzhemu8znce0g0ovulx.png	\N	\N	\N	FAGNIBO Félix	na@gmail.com	-16.34
303	Romano Floris Sèdjro	GOUDOU	\N	\N	2025-11-24 10:05:57	2025-11-24 10:05:57	M	15258	Cotonou	0144889244	0.00	f	f	\N	f	\N	\N	1	2011-04-13	3	21	https://res.cloudinary.com/marialain/image/upload/v1763978757/students_files/mlgt8lxoq18g7wsttlw3.png	\N	\N	\N	GOUDOU Roméo	na@gmail.com	-14.62
304	Barnay Naphan Vangelis	DENOU	\N	\N	2025-11-24 10:08:15	2025-11-24 10:08:15	M	25845	Cotonou	0197877815	0.00	f	f	\N	f	\N	\N	1	2009-03-03	3	21	https://res.cloudinary.com/marialain/image/upload/v1763978894/students_files/p1yowqjsdwpcavvquyw7.png	\N	\N	\N	DENOU Parfait	na@gmail.com	-16.73
305	Mahunan Désiré David	KOHONOU	\N	\N	2025-11-24 10:13:22	2025-11-24 10:13:22	M	1258	Cotonou	0197333444	0.00	f	f	\N	f	\N	\N	1	2008-03-15	3	21	https://res.cloudinary.com/marialain/image/upload/v1763979201/students_files/twuytvjofdj9likdzb4i.png	\N	\N	\N	KOHONOU janvier	na@gmail.com	-17.70
306	Destinée Premine	KLICO	\N	\N	2025-11-24 10:15:45	2025-11-24 10:15:45	F	1525	Cotonou	0196176074	0.00	f	f	\N	f	\N	\N	1	2011-05-08	3	21	https://res.cloudinary.com/marialain/image/upload/v1763979345/students_files/ldk0jsqfguslzqzv0vh3.png	\N	\N	\N	KLICO Guy	na@gmail.com	-14.55
\.


--
-- TOC entry 3722 (class 0 OID 41096)
-- Dependencies: 265
-- Data for Name: subject_averages; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.subject_averages (id, student_id, subject_id, average, weighted_average, trimestre, rank, academic_year_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3698 (class 0 OID 32898)
-- Dependencies: 241
-- Data for Name: subjects; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.subjects (id, name, created_at, updated_at, academic_year_id, classe_id, coefficient) FROM stdin;
1	Mathématiques	2025-10-19 14:46:22	2025-10-19 14:46:22	1	\N	1
2	Français	2025-10-29 10:59:53	2025-10-29 10:59:53	1	\N	1
6	Philosophie	2025-11-14 07:52:19	2025-11-14 07:52:19	1	\N	1
8	Histoire-Géographie	2025-11-14 07:54:41	2025-11-14 07:54:41	1	\N	1
4	Science de la Vie et de la Terre (SVT)	2025-11-14 07:51:07	2025-11-14 07:51:07	1	\N	1
5	Physique Chimie et Technologie (PCT)	2025-11-14 07:51:43	2025-11-14 07:51:43	1	\N	1
9	Economie	2025-11-14 07:56:55	2025-11-14 07:56:55	1	\N	1
10	Espagnol	2025-11-14 07:58:27	2025-11-14 07:58:27	1	\N	1
11	Education Physique et Sportive (EPS)	2025-11-14 07:59:13	2025-11-14 07:59:13	1	\N	1
12	Informatique	2025-11-14 08:00:20	2025-11-14 08:00:20	1	\N	1
3	Anglais	2025-11-05 14:21:16	2025-11-05 14:21:16	1	\N	1
13	ENSEIGNEMENTS SCIENTIFIQUES	2025-11-19 08:07:59	2025-11-19 08:07:59	1	\N	1
14	ENSEIGNEMENTS SCIENTIFIQUES SVT	2025-11-19 20:17:26	2025-11-19 20:17:26	1	\N	1
\.


--
-- TOC entry 3706 (class 0 OID 32975)
-- Dependencies: 249
-- Data for Name: teacher_invitations; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.teacher_invitations (id, user_id, censeur_id, token, accepted, created_at, updated_at, accepted_at, academic_year_id, classe_id) FROM stdin;
22	29	3	sFimJy2UhpCWb4lpZRFQjDDJJdA5DTBx	t	2025-10-29 12:56:41	2025-10-29 12:56:41	2025-10-29 12:58:01	1	2
31	38	4	pfbKoJEEofLrtXZo74HEfTAgU9348zJ3OsSDK9oE	t	2025-11-13 11:53:01	2025-11-13 11:53:01	2025-11-13 00:00:00	1	\N
32	39	4	0QoCE6Mzwf0g7q1BH8faA6QRpfpaDOHDcP9BDv82	t	2025-11-13 11:53:03	2025-11-13 11:53:03	2025-11-13 00:00:00	1	\N
33	40	4	EGH1a06IVakDSuKtrIBH09JQBxaQsChtCIRu3Yho	t	2025-11-13 11:53:04	2025-11-13 11:53:04	2025-11-13 00:00:00	1	\N
34	41	4	7KJdeoTwrvoMhqsODO4MClrDaYNMChQlFaZEt8YD	t	2025-11-13 11:53:06	2025-11-13 11:53:06	2025-11-13 00:00:00	1	\N
35	42	4	bR1VRkoXXX5pfQJG76DOe53duEqyNkn5ixU4ihJt	t	2025-11-13 11:53:08	2025-11-13 11:53:08	2025-11-13 00:00:00	1	\N
36	43	4	yfCrP6Cs5PdU5IJEumpY5oZyvmSJamTPfozceDux	t	2025-11-13 11:53:09	2025-11-13 11:53:09	2025-11-13 00:00:00	1	\N
37	44	4	sMQCYNk6zPyNJuVLi7vklFiQMt3JRJOs7eAW49Oj	t	2025-11-13 11:53:11	2025-11-13 11:53:11	2025-11-13 00:00:00	1	\N
38	45	4	5LOkqwqd997shRRCwcm6OxCIr1nOirsubSLwPuQf	t	2025-11-13 11:53:13	2025-11-13 11:53:13	2025-11-13 00:00:00	1	\N
39	46	4	Z34bvQKxFwxHZVhbru3hYouO69HyrgD7noVfzqNj	t	2025-11-13 11:53:14	2025-11-13 11:53:14	2025-11-13 00:00:00	1	\N
40	47	4	2VF1N4yY6IRWrEOMituNTwYl4kfq587QZtYr5bOf	t	2025-11-13 11:53:16	2025-11-13 11:53:16	2025-11-13 00:00:00	1	\N
41	48	4	Lo7cXCdebmPpw2dhK1QaO6133HZWiSO5lVo9wXN0	t	2025-11-13 11:53:18	2025-11-13 11:53:18	2025-11-13 00:00:00	1	\N
42	49	4	0bJ178Eo9wKDNcZ16dHH5cJZDKhVKmktrZvcMvIN	t	2025-11-13 11:53:19	2025-11-13 11:53:19	2025-11-13 00:00:00	1	\N
43	50	4	rm6MhtfyUubYabLCObHhf6OXJnYhJNAXc66AjDJE	t	2025-11-13 11:53:21	2025-11-13 11:53:21	2025-11-13 00:00:00	1	\N
44	51	4	bXKyrH3ElwSH7vdnAmo2bxHvuOg1K1i4iDzTC78V	t	2025-11-13 11:53:23	2025-11-13 11:53:23	2025-11-13 00:00:00	1	\N
45	52	4	IjCCWFYXPEwSq6YL3CFulRc6QqreMbXhbacp97zO	t	2025-11-13 11:53:24	2025-11-13 11:53:24	2025-11-13 00:00:00	1	\N
47	54	4	bYZq6iSBgh67DfJrkjrQNTA9O5vEipwCb9vEcaOW	t	2025-11-14 07:38:45	2025-11-14 07:38:45	2025-11-14 00:00:00	1	\N
48	55	4	lQNMA7XtjRV4pNq6I14XgTc2brvZW0W1shGvqP7k	t	2025-11-14 07:38:47	2025-11-14 07:38:47	2025-11-14 00:00:00	1	\N
49	56	4	LwcSf6Uj5Oi6GvMw64zGUAk80pmbaX9KhNVZvMeR	t	2025-11-14 07:38:48	2025-11-14 07:38:48	2025-11-14 00:00:00	1	\N
50	57	4	QOTNHNPnYPKfaQEDrhwAZicDW40X3wDpqGgFMSCi	t	2025-11-14 07:38:50	2025-11-14 07:38:50	2025-11-14 00:00:00	1	\N
51	58	4	4sq3R3WS9qi3hdAAvxI3vqpjdJ5JXQcECC4MgiOK	t	2025-11-14 07:38:52	2025-11-14 07:38:52	2025-11-14 00:00:00	1	\N
52	59	4	z71n5h26jBslonlw3EwLQENbGxaZ5Wrdro9dBQ2S	t	2025-11-14 07:38:54	2025-11-14 07:38:54	2025-11-14 00:00:00	1	\N
53	60	4	JORHVub8SYwO1B8DMbE3HJuIcL0m1SNZcg3AiidP	t	2025-11-14 07:38:56	2025-11-14 07:38:56	2025-11-14 00:00:00	1	\N
54	61	4	7uWVpcXP1zlKBld9omOgXP5E7tEfDj5yAvUEedrs	t	2025-11-14 07:38:58	2025-11-14 07:38:58	2025-11-14 00:00:00	1	\N
55	65	4	2qr1Xb7ge1OSOGId7AgSNkpj4l6ycon9sL9maQUG	t	2025-11-14 07:42:29	2025-11-14 07:42:29	2025-11-14 00:00:00	1	\N
56	66	4	Pi1MwhYGw4KXkKPCGi9AWpYBQ5gXzJhuksUQHdvj	t	2025-11-14 07:42:31	2025-11-14 07:42:31	2025-11-14 00:00:00	1	\N
57	67	4	80MbYleQVPDVDzkvaJ1pzFmrX6R6CAnp	t	2025-11-17 07:46:01	2025-11-17 07:46:01	2025-11-18 00:00:00	1	\N
59	69	4	l21SgBVV1YYjbotOSUXqTAbKLqJgWrDw	t	2025-11-18 15:48:58	2025-11-18 15:48:58	2025-11-18 15:48:58	1	\N
60	70	4	8264KMhSKTRsQALIkp6x6i0pawcuXHCw	t	2025-11-18 15:49:02	2025-11-18 15:49:02	2025-11-18 15:49:02	1	\N
61	71	4	ApDc3jPNVUTkn3BF98LCU8Bk9MIhVRYG	t	2025-11-18 15:49:06	2025-11-18 15:49:06	2025-11-18 15:49:06	1	\N
62	72	4	onjc1EgKPUMlhGDtuWuFNRBGMi3ZuZeq	t	2025-11-18 15:49:10	2025-11-18 15:49:10	2025-11-18 15:49:10	1	\N
63	73	4	t3QD8VTp37eCxS4zYs3EeZoyEkn24Icn	t	2025-11-18 15:49:14	2025-11-18 15:49:14	2025-11-18 15:49:14	1	\N
64	74	4	OSYyh3r6z2gDqGyq6mQ0Ua9oGWDWKhkC	t	2025-11-18 15:49:18	2025-11-18 15:49:18	2025-11-18 15:49:18	1	\N
65	75	4	WYSRvY75FwzUfAyamfhylQwOkSVWvEwr	t	2025-11-18 15:49:22	2025-11-18 15:49:22	2025-11-18 15:49:22	1	\N
66	76	4	2DLsXOznuJCzzs27IIAOAjbx0aG4ths0	t	2025-11-18 15:49:26	2025-11-18 15:49:26	2025-11-18 15:49:26	1	\N
67	77	4	KO6qILOwlL2XUJeoGjC862rsTDL26mIl	t	2025-11-18 15:49:29	2025-11-18 15:49:29	2025-11-18 15:49:29	1	\N
68	78	4	UY8ezQA9fqI16xb8wtE6mNZL5yB9xk6H	t	2025-11-18 15:49:33	2025-11-18 15:49:33	2025-11-18 15:49:33	1	\N
69	79	4	tVxem6GJ7obuSz0nJOL0NYLvg2H8XpCm	t	2025-11-18 15:49:37	2025-11-18 15:49:37	2025-11-18 15:49:37	1	\N
70	80	4	Y6T3nbSBAPAc9J0aiN176Gs0MODeoYDX	t	2025-11-18 15:49:41	2025-11-18 15:49:41	2025-11-18 15:49:41	1	\N
71	81	4	6WKKuYRxxGFon71BtnkVvNvLGJLDxAOO	t	2025-11-18 15:49:45	2025-11-18 15:49:45	2025-11-18 15:49:45	1	\N
72	82	4	9JubtTaVriLQ00Kf2nZ5OAcPMNQvusMb	t	2025-11-18 15:49:49	2025-11-18 15:49:49	2025-11-18 15:49:49	1	\N
73	83	4	UpEGpMOPJB6rz7zmDBDI1bBqyANAr4WG	t	2025-11-18 15:49:53	2025-11-18 15:49:53	2025-11-18 15:49:53	1	\N
74	84	4	H5WZUiSWVkOsLYCQQHllDlrdT7hYg0BE	t	2025-11-18 15:49:57	2025-11-18 15:49:57	2025-11-18 15:49:57	1	\N
75	85	4	dTA3Zda0gVQlJdlyQ1cw8uddbOsPCNo4	t	2025-11-18 15:50:01	2025-11-18 15:50:01	2025-11-18 15:50:01	1	\N
76	86	4	HbWajLkdEEk11dYBEoGKsmqn1cE58bmm	t	2025-11-18 15:50:05	2025-11-18 15:50:05	2025-11-18 15:50:05	1	\N
\.


--
-- TOC entry 3710 (class 0 OID 33019)
-- Dependencies: 253
-- Data for Name: timetables; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.timetables (id, class_id, teacher_id, subject_id, day, start_time, end_time, created_at, updated_at, academic_year_id) FROM stdin;
121	6	50	11	Jeudi	07:30:00	09:30:00	2025-11-24 09:21:41	2025-11-24 09:21:41	1
60	5	41	3	Lundi	14:00:00	16:00:00	2025-11-17 07:59:14	2025-11-17 07:59:14	1
3	3	39	2	Mardi	07:00:00	10:00:00	2025-11-14 13:29:58	2025-11-14 13:29:58	1
4	3	39	2	Vendredi	10:00:00	12:00:00	2025-11-14 13:30:46	2025-11-14 13:30:46	1
5	3	43	3	Lundi	14:00:00	16:00:00	2025-11-14 13:35:58	2025-11-14 13:35:58	1
6	3	43	3	Lundi	14:00:00	16:00:00	2025-11-14 13:36:05	2025-11-14 13:36:05	1
7	3	43	3	Jeudi	10:00:00	12:00:00	2025-11-14 13:37:00	2025-11-14 13:37:00	1
10	3	58	5	Jeudi	14:00:00	17:00:00	2025-11-14 13:42:11	2025-11-14 13:42:11	1
11	5	38	2	Mercredi	07:00:00	10:00:00	2025-11-14 13:46:48	2025-11-14 13:46:48	1
12	5	38	2	Vendredi	15:00:00	17:00:00	2025-11-14 13:47:40	2025-11-14 13:47:40	1
61	5	67	8	Mardi	07:00:00	10:00:00	2025-11-17 08:00:07	2025-11-17 08:00:07	1
62	5	44	4	Jeudi	07:00:00	10:00:00	2025-11-17 08:01:06	2025-11-17 08:01:06	1
17	4	40	2	Lundi	10:00:00	13:00:00	2025-11-14 14:05:47	2025-11-14 14:05:47	1
18	4	57	5	Lundi	07:00:00	10:00:00	2025-11-14 14:06:48	2025-11-14 14:06:48	1
19	4	57	5	Vendredi	07:00:00	10:00:00	2025-11-14 14:08:04	2025-11-14 14:08:04	1
20	4	65	8	Lundi	14:00:00	16:00:00	2025-11-14 14:08:44	2025-11-14 14:08:44	1
21	4	65	8	Mardi	14:00:00	17:00:00	2025-11-14 14:09:20	2025-11-14 14:09:20	1
24	4	48	1	Mardi	10:00:00	12:30:00	2025-11-14 14:11:50	2025-11-14 14:11:50	1
25	4	48	1	Jeudi	14:00:00	17:00:00	2025-11-14 14:12:22	2025-11-14 14:12:22	1
26	4	46	6	Jeudi	07:00:00	12:00:00	2025-11-14 14:13:30	2025-11-14 14:13:30	1
27	4	55	3	Vendredi	10:00:00	13:00:00	2025-11-14 14:14:22	2025-11-14 14:14:22	1
30	12	55	3	Vendredi	10:00:00	13:00:00	2025-11-14 14:17:43	2025-11-14 14:17:43	1
33	12	40	2	Jeudi	14:00:00	17:00:00	2025-11-14 14:20:48	2025-11-14 14:20:48	1
34	12	40	2	Lundi	10:00:00	13:00:00	2025-11-14 14:21:19	2025-11-14 14:21:19	1
35	12	46	6	Jeudi	07:00:00	12:00:00	2025-11-14 14:22:39	2025-11-14 14:22:39	1
37	12	59	10	Lundi	07:00:00	10:00:00	2025-11-14 14:50:45	2025-11-14 14:50:45	1
38	12	65	8	Lundi	14:00:00	16:00:00	2025-11-14 14:51:35	2025-11-14 14:51:35	1
39	12	65	8	Mardi	14:00:00	17:00:00	2025-11-14 14:53:26	2025-11-14 14:53:26	1
63	5	41	3	Jeudi	10:00:00	12:00:00	2025-11-17 08:01:46	2025-11-17 08:01:46	1
49	23	47	6	Mardi	14:00:00	17:00:00	2025-11-17 07:26:32	2025-11-17 07:26:32	1
65	6	67	8	Lundi	10:00:00	12:30:00	2025-11-17 14:42:40	2025-11-17 14:42:40	1
56	3	67	8	Lundi	07:00:00	10:00:00	2025-11-17 07:48:26	2025-11-17 07:48:26	1
57	3	50	11	Lundi	10:00:00	12:00:00	2025-11-17 07:49:30	2025-11-17 07:49:30	1
59	5	50	11	Lundi	08:00:00	10:00:00	2025-11-17 07:56:50	2025-11-17 07:56:50	1
70	6	38	2	Mercredi	10:00:00	12:00:00	2025-11-17 14:48:50	2025-11-17 14:48:50	1
71	6	50	11	Jeudi	07:30:00	09:30:00	2025-11-17 14:49:56	2025-11-17 14:49:56	1
72	6	50	11	Jeudi	07:30:00	09:30:00	2025-11-17 14:51:14	2025-11-17 14:51:14	1
74	6	38	2	Jeudi	14:00:00	17:00:00	2025-11-17 14:54:20	2025-11-17 14:54:20	1
50	23	51	2	Mercredi	07:00:00	09:00:00	2025-11-17 07:27:30	2025-11-18 02:43:54	1
55	23	51	2	Jeudi	08:00:00	10:00:00	2025-11-17 07:33:39	2025-11-18 02:45:46	1
64	6	60	10	Lundi	07:00:00	10:00:00	2025-11-17 14:41:54	2025-11-20 06:08:42	1
80	7	40	4	Lundi	10:00:00	12:30:00	2025-11-17 15:16:06	2025-11-17 15:16:06	1
81	4	86	4	Mardi	07:00:00	10:00:00	2025-11-18 16:45:35	2025-11-18 16:45:35	1
82	4	86	4	Mercredi	10:00:00	13:00:00	2025-11-18 16:46:11	2025-11-18 16:46:11	1
83	23	81	5	Lundi	07:00:00	09:30:00	2025-11-19 07:43:22	2025-11-19 07:43:22	1
84	23	83	3	Lundi	14:00:00	17:00:00	2025-11-19 07:44:12	2025-11-19 07:44:12	1
85	23	80	4	Mardi	07:00:00	09:00:00	2025-11-19 07:49:56	2025-11-19 07:49:56	1
86	23	80	4	Mardi	07:00:00	09:00:00	2025-11-19 07:49:57	2025-11-19 07:49:57	1
87	23	72	1	Mardi	09:00:00	10:00:00	2025-11-19 07:50:59	2025-11-19 07:50:59	1
88	23	79	11	Mardi	15:00:00	17:00:00	2025-11-19 07:52:56	2025-11-19 07:52:56	1
89	23	51	2	Mercredi	07:00:00	09:00:00	2025-11-19 07:58:03	2025-11-19 07:58:03	1
90	23	80	4	Mercredi	10:30:00	12:30:00	2025-11-19 07:58:51	2025-11-19 07:58:51	1
92	23	80	4	Mercredi	10:00:00	12:00:00	2025-11-19 08:01:14	2025-11-19 08:01:14	1
93	23	80	4	Mercredi	10:30:00	12:30:00	2025-11-19 08:02:06	2025-11-19 08:02:06	1
96	23	61	8	Jeudi	14:00:00	17:00:00	2025-11-19 08:11:27	2025-11-19 08:11:27	1
97	3	74	1	Mardi	10:00:00	12:00:00	2025-11-19 08:14:38	2025-11-19 08:14:38	1
98	3	74	1	Vendredi	07:00:00	10:00:00	2025-11-19 08:15:31	2025-11-19 08:15:31	1
99	5	73	1	Mardi	10:00:00	12:30:00	2025-11-19 08:18:09	2025-11-19 08:18:09	1
100	5	73	1	Vendredi	10:00:00	12:30:00	2025-11-19 08:26:11	2025-11-19 08:26:11	1
101	5	78	5	Vendredi	07:00:00	09:00:00	2025-11-19 08:28:16	2025-11-19 08:28:16	1
102	5	78	5	Mercredi	10:00:00	12:00:00	2025-11-19 08:29:28	2025-11-19 08:29:28	1
103	6	82	3	Lundi	14:00:00	16:00:00	2025-11-19 08:32:10	2025-11-19 08:32:10	1
104	6	74	1	Mardi	07:00:00	10:00:00	2025-11-19 08:33:13	2025-11-19 08:33:13	1
105	6	78	5	Mardi	14:00:00	17:00:00	2025-11-19 08:35:14	2025-11-19 08:35:14	1
106	6	82	3	Mercredi	08:00:00	10:00:00	2025-11-19 08:36:13	2025-11-19 08:36:13	1
107	6	44	4	Jeudi	10:00:00	12:30:00	2025-11-19 08:38:32	2025-11-19 08:38:32	1
108	6	50	11	Jeudi	07:30:00	09:30:00	2025-11-19 08:40:01	2025-11-19 08:40:01	1
109	6	50	11	Jeudi	07:30:00	09:30:00	2025-11-19 08:41:13	2025-11-19 08:41:13	1
111	6	74	1	Vendredi	10:00:00	12:30:00	2025-11-19 08:52:08	2025-11-19 08:52:08	1
112	6	38	2	Vendredi	15:00:00	17:00:00	2025-11-19 08:52:55	2025-11-19 08:52:55	1
113	23	81	13	Vendredi	14:00:00	15:00:00	2025-11-19 20:12:43	2025-11-19 20:12:43	1
94	23	72	1	Vendredi	07:00:00	10:00:00	2025-11-19 08:06:55	2025-11-19 22:16:28	1
119	23	67	11	Vendredi	10:30:00	12:00:00	2025-11-20 06:13:16	2025-11-20 06:15:11	1
\.


--
-- TOC entry 3679 (class 0 OID 32776)
-- Dependencies: 222
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: neondb_owner
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role_id, profile_photo, gender, phone, marital_status, address, birth_date, birth_place, nationality, id_card, birth_certificate, diploma, ifu_number, ifu, rib, rib_document, id_card_file, birth_certificate_file, diploma_file, ifu_file, rib_file, id_card_number) FROM stdin;
1	Test User	test@example.com	2025-10-04 22:28:19	$2y$12$YFVT92gXuHTiyViQcYl6J.Vbjqfjw2hiyolVsgHbw.TCbKR7Flo1W	K9JpxSQUYR	2025-10-04 22:28:19	2025-10-04 22:28:19	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
3	Directeur primaire	directeur_primaire@gmail.com	\N	$2y$12$3RCmXAb9pTH1xjKwHVoy4.vjvB63qCmHIQxuhDLZDgOp315TDISfe	\N	2025-10-04 22:47:58	2025-10-04 22:47:58	3	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
5	Surveillant	surveillant@gmail.com	\N	$2y$12$IkUmwkKSgMz4LWjn8HeCKupuLttQEhGrh//3toIaHy26SzMFEhSf2	\N	2025-10-04 22:48:04	2025-10-04 22:48:04	5	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
6	Secrétaire	secretaire@gmail.com	\N	$2y$12$JIPIav.gez4oJw3av66XWOh21KcbuRYN6OQGFDgb7hp1smO9zEsBu	47a2zNtQzk4Qnprfj7bVtQqOQ2dzL6Q9L4Ya88SD4CgQG0WY6JCan4NrQ5n0	2025-10-04 22:48:07	2025-10-29 02:00:47	6	profiles/yvwm6rbbnfafqzdfsmjs	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	https://res.cloudinary.com/marialain/image/upload/v1761701797/enseignants/cvolcx7pg6lc5ofivifw.jpg	https://res.cloudinary.com/marialain/image/upload/v1761701799/enseignants/pspqgahbjjiguqnenwll.jpg	https://res.cloudinary.com/marialain/image/upload/v1761702873/enseignants/maxjpo4cvwjg9rdhtcjw.jpg	\N	\N	\N
7	Admin MARI ALAIN	admin@mariealain	\N	$2y$12$nJAdDojnMk.wF9JovQhYTOzwO/vP12o87ZLeFuQtDmL157gpWfiv6	\N	2025-10-04 22:48:43	2025-10-27 13:48:41	7	profiles/hdtegkzHYH7sJ2QE3gnRIKsM2Gw9dLoZUUXcOu3d.jpg	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
40	Justin	justin@gmail.com	\N	$2y$12$dRxdyuDrWuFHRNQi9eh5ruemlkhv.NuNZZsEomyWg2/f/QaRL6vqG	\N	2025-11-13 11:53:04	2025-11-13 11:53:04	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
29	KONNON Abel	dglinj25@gmail.com	\N	$2y$12$U7XDzDGzcM.TTD57bh3zcebkvDImhQkTVqX88rssM33es2ouWiEO6	\N	2025-10-29 12:56:39	2025-10-29 12:56:39	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
41	Ayouba	ayouba@gmail.com	\N	$2y$12$Uso0OCwJr47tO2rz0Sl.sOVXAQnNoyEkpvY8TYYnm.lk2MOkB2Gs.	\N	2025-11-13 11:53:05	2025-11-13 11:53:05	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
42	Adjidja	adjidja@gmail.com	\N	$2y$12$uJqQ/yTzMvjYq2Ut.YduJeVTsdLrpo0GTktDSHepfam/BiXrCKUtC	\N	2025-11-13 11:53:07	2025-11-13 11:53:07	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
38	Oumoroucsma	oumoroucsma@gmail.com	\N	$2y$12$heqUq0cRuiFmK7Xn5aFWeeQZRgOx/s1b6TkCP3t2./l5QOPGJk54e	\N	2025-11-13 11:52:58	2025-11-13 11:52:58	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
39	Ponsot	ponsot@gmail.com	\N	$2y$12$t2NkhJ5DqInCifq0.2Onde.0Dgcjt7g8fRaUwmG0A9/06rP515j4a	\N	2025-11-13 11:53:02	2025-11-13 11:53:02	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
43	Doris	doris@gmail.com	\N	$2y$12$UE0IvMbzqAfS2XhLwXe3U.mVZj7E12MJ/CHMz2vqLVIt4p55O5ywC	\N	2025-11-13 11:53:09	2025-11-13 11:53:09	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
44	Bernard	bernard@gmail.com	\N	$2y$12$LHfog4Oafxsi0hxCb6SWRuwc2Qr6JkoHxe5YsLMz198F4S2rRbsZm	\N	2025-11-13 11:53:10	2025-11-13 11:53:10	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
45	Paul	paul@gmail.com	\N	$2y$12$VMzK4UEIb9pt.d0EBdZqJO/HcypWrus1Z5cyEMBYmQsiPeA1pymvS	\N	2025-11-13 11:53:12	2025-11-13 11:53:12	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
46	Ayinon	ayinon@gmail.com	\N	$2y$12$k7gUeJXxequM2tZknEojk.WKpEVuTuZN1.pATvnDz43BnkY5jjjgK	\N	2025-11-13 11:53:14	2025-11-13 11:53:14	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
47	Adi	adi@gmail.com	\N	$2y$12$oSj1F2H78PW8DOhb5JanB.UKGDHaWm6dybcJhBEIjY2yHhC3ZVzk2	\N	2025-11-13 11:53:15	2025-11-13 11:53:15	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
48	Awo	awo@gmail.com	\N	$2y$12$witykU1jgwg4tUfjw5h2TOvfX/yX.4ewBQ/1zOKAWV7TfP7TyMUqS	\N	2025-11-13 11:53:17	2025-11-13 11:53:17	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
49	Alexandrehouetomenon	alexandrehouetomenon@gmail.com	\N	$2y$12$RIFNmsUMun/x4RvPtRJ8cu3gkpy/UlCnuVo.4y/cWDiu0JaD1i4.e	\N	2025-11-13 11:53:19	2025-11-13 11:53:19	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
50	Mathurin	mathurin@gmail.com	\N	$2y$12$hkcR.DUOKf4zYFcaoRl.gOeZE7w8ranT4rkDoI92EFPZ3rhGl2RFe	\N	2025-11-13 11:53:20	2025-11-13 11:53:20	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
51	Dedeericcsma	dedeericcsma@gmail.com	\N	$2y$12$LGGBrN7g71VBj3PnaCi3ce7xi9yptSlMcqHaE7FCTTu3Ft85POu8m	\N	2025-11-13 11:53:22	2025-11-13 11:53:22	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
82	Aimetantchinita	aimetantchinita@gmail.com	\N	$2y$12$YHriK8viPKk14O8gMBwCkeXPNWj12DqiRjAYe2usnWw6RnE9JOcKS	\N	2025-11-18 15:49:49	2025-11-18 15:49:49	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
54	Hountos	hountos@gmail.com	\N	$2y$12$RIU7ZmR9Yuu1MjNWKLT6S.bVvSsGc3ik8lpDfE8wvA62Y86xELxy.	\N	2025-11-14 07:38:41	2025-11-14 07:38:41	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
55	Ericdossou2022	ericdossou2022@gmail.com	\N	$2y$12$.9KYGgCyt2eVaaYrYeBMCef3v9etIedxxF7Q0HbEjgvA.Pem1LT1e	\N	2025-11-14 07:38:46	2025-11-14 07:38:46	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
56	Houindotes	houindotes@gmail.com	\N	$2y$12$i47FyCNEWXdZOLZ8PKthmOS81i/OrTLvTGPqYghWluSHSxG3PQsES	\N	2025-11-14 07:38:48	2025-11-14 07:38:48	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
57	Tokpeeldadi1	tokpeeldadi1@gmail.com	\N	$2y$12$WNJtd304ql5AEurjfnDgaO/rAF7McNVt18H9uDpQ/YiViaVz4tDaO	\N	2025-11-14 07:38:49	2025-11-14 07:38:49	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
60	Yahouedehoumars1234	yahouedehoumars1234@gmail	\N	$2y$12$qYvJzWBAKl3CF/NXQKmpluqBKXjj2eq21DRoM7TKVtpzsq8tbabju	\N	2025-11-14 07:38:55	2025-11-14 07:38:55	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
61	Martinawononcsma	martinawononcsma@gmail	\N	$2y$12$iGc2geTYM01Fh1kt3yfby.rK3bb1WpRDE4c7NywV.aBIbHLEn0lpm	\N	2025-11-14 07:38:57	2025-11-14 07:38:57	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
84	Maximehounkanlo0	maximehounkanlo0@gmail.com	\N	$2y$12$jeatLjIdt9G5WfqZPvduuuLuMlN3svBNNgdu0Hl0ypdgT3h21GcQC	nQboAMM3nlmC6cH4W253Y80Z0qr8zPISvWHnQAhJyhmyNcxQqFnkc4eolUvr	2025-11-18 15:49:57	2025-11-18 15:49:57	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
67	AMADOU	amadouabdoulbassith@gmail	\N	$2y$12$4PP3LW6VN8sG2QLV.9mOWuGz5GhLFsxZFv26EjmipMylbIXqjCNM.	\N	2025-11-17 07:46:00	2025-11-17 07:46:00	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
85	Sourouavoc	sourouavoc@gmail.com	\N	$2y$12$DvwVdTaNQqgojObNj83sF.63x8Ev3fNFG2aAW/gbvdOgO0MDNatc2	\N	2025-11-18 15:50:01	2025-11-18 15:50:01	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
69	Amadouabdoulbassith	amadouabdoulbassith@gmail.com	\N	$2y$12$6G5yfz9qpIMQFoqd1sA9TO/lQktajNCYT6drwPuMUcFt1gdqTITPm	Ym1BDybL87sAtSU2IAKSlBttaPhxqT8zxV0NaVWgqK7AMRZQr7THFBAmGNFV	2025-11-18 15:48:57	2025-11-18 15:48:57	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
65	Donatienzounma	donatienzounma@gmail.com	\N	$2y$12$qe0JsfE5C8E3TbJWIw8AE.jIRsoTecHuUXmbndCUr9sa/W0Vlewry	MUk1MYGVR50UXi146RjniarfRcXTO8V71gykclMTp29W9vErf2Mr8jy9tBDh	2025-11-14 07:42:26	2025-11-14 07:42:26	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
70	Fadalicsma	fadalicsma@gmail.com	\N	$2y$12$Q3MIhaRsny1SM1ssCeVpseBD8KX3qsnY6qZG2RyjdKWUxNBiXrZ8.	\N	2025-11-18 15:49:01	2025-11-18 15:49:01	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
71	Hessoujosue6726	hessoujosue6726@gmail.com	\N	$2y$12$XtHVxeKpE66qfFwX/p4Ym.jEsFGT0m9BJZPYZjCFrQQQaP6pzYsRq	\N	2025-11-18 15:49:05	2025-11-18 15:49:05	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
72	Oscar.godonou	oscar.godonou@yahoo.com	\N	$2y$12$zYJg/IluwRGcYUR/MozbF.SwDBP6evEc9WHJFxRgvwXRgxsX3quEC	\N	2025-11-18 15:49:09	2025-11-18 15:49:09	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
73	Aguemonconstant1	aguemonconstant1@gmail.com	\N	$2y$12$sjXmwLjMaI.FB4JZ04rzEuC29CjWCeMpN6uscVEBa60gFJPOsrRYa	\N	2025-11-18 15:49:13	2025-11-18 15:49:13	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
74	Fabiendavo	fabiendavo@gmail.com	\N	$2y$12$XHzPcJVlqrb5ntlDI3zBfuQF.xBfEeUufAGv4TASxmxr5PfAroUWG	\N	2025-11-18 15:49:17	2025-11-18 15:49:17	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
58	Philemoahotonguidi	philemoahotonguidi@gmail.com	\N	$2y$12$1mZC38qnP7VVnKA6a1EKj.S.YCTImQsorcUHcEHbiLe3ZW.vOn1jW	vWSxkyt7jj13ZgcBeebnrdBPGsXawqt1aR4F7ukbllLZlZLl1gfglNuRwr1J	2025-11-14 07:38:51	2025-11-14 07:38:51	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
75	Haroldthossou	haroldthossou@gmail.com	\N	$2y$12$aAKFfq2y8fqB0jqEp/RNqe2hgjQEx9OC4OcopW.JK1kFLaquCLJt.	\N	2025-11-18 15:49:21	2025-11-18 15:49:21	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
76	Okegouc	okegouc@gmail.com	\N	$2y$12$dV/iiTa7n0ufYXVjpHy5OOO8YWOibM8M3hsDlIBCuaTUODx4lDIH6	\N	2025-11-18 15:49:25	2025-11-18 15:49:25	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
77	Ali87charles	ali87charles@gmail.com	\N	$2y$12$qsgY6Uwik7cfUY1qS2l1WuBKtr/mqOBtw4IGZmz07EI85ZxdWUVnq	\N	2025-11-18 15:49:29	2025-11-18 15:49:29	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
78	Florenttitekoun316	florenttitekoun316@gmail.com	\N	$2y$12$Mb1UdBMN52uXcrFxQURKRu0xUcL5uuVRMQup8phU7hj6/jGfWyJo6	\N	2025-11-18 15:49:33	2025-11-18 15:49:33	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
79	Ahozegni80	ahozegni80@gmail.com	\N	$2y$12$Ync5GAADXQbGfpZgonM8t.LZjnH.2oF1EL4PMhyWtiQulL8jP5esu	\N	2025-11-18 15:49:37	2025-11-18 15:49:37	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
81	Sebastiendinok	sebastiendinok@gmail.com	\N	$2y$12$eJLThQtMFo/yUGCFooPLiOsiwxbRB8ejSAptq77ledy32e9Ueobyq	\N	2025-11-18 15:49:45	2025-11-18 15:49:45	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
66	Wencesfr	wencesfr@gmail.com	\N	$2y$12$RVPwlG8FuY1vofpjmsbMWuleS3sre2i/3g5XG.KHWFBe97s3ItKQ2	\N	2025-11-14 07:42:30	2025-11-20 17:39:33	8	profiles/i9ogrbeklflhoqkphtne	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
86	Astrid1200	astrid1200@gmail.com	\N	$2y$12$fQNhlOnUm4b5dTYzsH8zYeI5AxHr2lvRQtwV3ibO5RyCna2zNEORG	qxmli3lAXs2XfttrgxunB1p0YZesB29TeurJYXXj1hRqGFKpykZv8oFqp9YQ	2025-11-18 15:50:05	2025-11-18 15:50:05	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
80	Ulvagoss	ulvagoss@gmail.com	\N	$2y$12$fu6cY5WBBWmIHgk1v4RRRueNNgyZWBMdR1EPTLhH6OMFlsMQ0CSfS	z8CtRfKaJvuYpJxbRJePFUikQRFgN77eAmnyslM4yzq3isg6ZK66Yq5BSMPc	2025-11-18 15:49:41	2025-11-18 15:49:41	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
52	Sebastiencsma	sebastiencsma@gmail.com	\N	$2y$12$sWM.XcRbRqWDmmH5pJ7uW.DDmE8PpNXugNTlyRlXUrhOqJVakNprG	yhgr6hT2tGlzqNiN6BWdgElJCsYBSkWZ8sxk67fxFdmwVLdFAtY9lIiQOYhs	2025-11-13 11:53:24	2025-11-13 11:53:24	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
83	Edahkingslim	edahkingslim@gmail.com	\N	$2y$12$9wvhXbyl5HO.EM5NPMPjpOkEIITk1F3IH3Y4c5wSD6qMLPEEdfJ4S	n5vfW666pddJFvv0HwtBu8W3ZOFUGcpptPiyjbmZqnAeUYv2nbrrXHJFhgRw	2025-11-18 15:49:53	2025-11-18 15:49:53	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
59	Judithsagnon	judithsagnon@gmail.com	\N	$2y$12$WWlPrEehHQ2BErDFHFWwM.AZPO92.pNeUv1nDwaXy2mcJbkR/BXOe	vAZ6wJqGlrvXQCmEaX86PMZjeIKXQzepvsHo8bjtzMjPoU64HR7BkiAEfIfZ	2025-11-14 07:38:53	2025-11-14 07:38:53	8	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
4	Censeur	censeur@gmail.com	\N	$2y$12$.9vN7KRyLgd/4kH/rP5YrO/cjWkF0GQp30Da3UWRYIZfe8nMzEC4a	GTQGi3H2M1BUx5W7pwp07HT6falkxH5ePoC97NBwHiqs7rJmOe2wua9I4RcC	2025-10-04 22:48:01	2025-11-12 12:35:08	4	\N	M	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- TOC entry 3766 (class 0 OID 0)
-- Dependencies: 234
-- Name: academic_years_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.academic_years_id_seq', 1, true);


--
-- TOC entry 3767 (class 0 OID 0)
-- Dependencies: 272
-- Name: cahier_de_texte_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.cahier_de_texte_id_seq', 6, true);


--
-- TOC entry 3768 (class 0 OID 0)
-- Dependencies: 250
-- Name: class_teacher_subject_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.class_teacher_subject_id_seq', 124, true);


--
-- TOC entry 3769 (class 0 OID 0)
-- Dependencies: 238
-- Name: classes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.classes_id_seq', 27, true);


--
-- TOC entry 3770 (class 0 OID 0)
-- Dependencies: 258
-- Name: conducts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.conducts_id_seq', 1, false);


--
-- TOC entry 3771 (class 0 OID 0)
-- Dependencies: 244
-- Name: enrollments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.enrollments_id_seq', 1, false);


--
-- TOC entry 3772 (class 0 OID 0)
-- Dependencies: 236
-- Name: entities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.entities_id_seq', 3, true);


--
-- TOC entry 3773 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 3774 (class 0 OID 0)
-- Dependencies: 262
-- Name: grades_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.grades_id_seq', 103, true);


--
-- TOC entry 3775 (class 0 OID 0)
-- Dependencies: 246
-- Name: invitations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.invitations_id_seq', 1, false);


--
-- TOC entry 3776 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- TOC entry 3777 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.migrations_id_seq', 59, true);


--
-- TOC entry 3778 (class 0 OID 0)
-- Dependencies: 274
-- Name: note_edit_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.note_edit_permissions_id_seq', 1, true);


--
-- TOC entry 3779 (class 0 OID 0)
-- Dependencies: 270
-- Name: note_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.note_permissions_id_seq', 36, true);


--
-- TOC entry 3780 (class 0 OID 0)
-- Dependencies: 256
-- Name: punishments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.punishments_id_seq', 1, false);


--
-- TOC entry 3781 (class 0 OID 0)
-- Dependencies: 232
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.roles_id_seq', 8, true);


--
-- TOC entry 3782 (class 0 OID 0)
-- Dependencies: 260
-- Name: schedules_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.schedules_id_seq', 1, false);


--
-- TOC entry 3783 (class 0 OID 0)
-- Dependencies: 268
-- Name: student_annual_averages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.student_annual_averages_id_seq', 1, false);


--
-- TOC entry 3784 (class 0 OID 0)
-- Dependencies: 254
-- Name: student_payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.student_payments_id_seq', 322, true);


--
-- TOC entry 3785 (class 0 OID 0)
-- Dependencies: 266
-- Name: student_trimestre_averages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.student_trimestre_averages_id_seq', 1, false);


--
-- TOC entry 3786 (class 0 OID 0)
-- Dependencies: 242
-- Name: students_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.students_id_seq', 306, true);


--
-- TOC entry 3787 (class 0 OID 0)
-- Dependencies: 264
-- Name: subject_averages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.subject_averages_id_seq', 1, false);


--
-- TOC entry 3788 (class 0 OID 0)
-- Dependencies: 240
-- Name: subjects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.subjects_id_seq', 14, true);


--
-- TOC entry 3789 (class 0 OID 0)
-- Dependencies: 248
-- Name: teacher_invitations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.teacher_invitations_id_seq', 76, true);


--
-- TOC entry 3790 (class 0 OID 0)
-- Dependencies: 252
-- Name: timetables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.timetables_id_seq', 121, true);


--
-- TOC entry 3791 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: neondb_owner
--

SELECT pg_catalog.setval('public.users_id_seq', 86, true);


--
-- TOC entry 3391 (class 2606 OID 24587)
-- Name: users_sync users_sync_pkey; Type: CONSTRAINT; Schema: neon_auth; Owner: neondb_owner
--

ALTER TABLE ONLY neon_auth.users_sync
    ADD CONSTRAINT users_sync_pkey PRIMARY KEY (id);


--
-- TOC entry 3422 (class 2606 OID 32863)
-- Name: academic_years academic_years_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.academic_years
    ADD CONSTRAINT academic_years_pkey PRIMARY KEY (id);


--
-- TOC entry 3407 (class 2606 OID 32815)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 3405 (class 2606 OID 32808)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 3470 (class 2606 OID 254003)
-- Name: cahier_de_texte cahier_de_texte_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte
    ADD CONSTRAINT cahier_de_texte_pkey PRIMARY KEY (id);


--
-- TOC entry 3446 (class 2606 OID 33002)
-- Name: class_teacher_subject class_teacher_subject_class_id_teacher_id_subject_id_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject
    ADD CONSTRAINT class_teacher_subject_class_id_teacher_id_subject_id_unique UNIQUE (class_id, teacher_id, subject_id);


--
-- TOC entry 3448 (class 2606 OID 33000)
-- Name: class_teacher_subject class_teacher_subject_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject
    ADD CONSTRAINT class_teacher_subject_pkey PRIMARY KEY (id);


--
-- TOC entry 3428 (class 2606 OID 32881)
-- Name: classes classes_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_pkey PRIMARY KEY (id);


--
-- TOC entry 3456 (class 2606 OID 41023)
-- Name: conducts conducts_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.conducts
    ADD CONSTRAINT conducts_pkey PRIMARY KEY (id);


--
-- TOC entry 3436 (class 2606 OID 32922)
-- Name: enrollments enrollments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_pkey PRIMARY KEY (id);


--
-- TOC entry 3424 (class 2606 OID 32872)
-- Name: entities entities_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_pkey PRIMARY KEY (id);


--
-- TOC entry 3426 (class 2606 OID 32874)
-- Name: entities entities_slug_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.entities
    ADD CONSTRAINT entities_slug_unique UNIQUE (slug);


--
-- TOC entry 3414 (class 2606 OID 32842)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 3416 (class 2606 OID 32844)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 3460 (class 2606 OID 41079)
-- Name: grades grades_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grades
    ADD CONSTRAINT grades_pkey PRIMARY KEY (id);


--
-- TOC entry 3438 (class 2606 OID 32947)
-- Name: invitations invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_pkey PRIMARY KEY (id);


--
-- TOC entry 3440 (class 2606 OID 32964)
-- Name: invitations invitations_token_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_token_unique UNIQUE (token);


--
-- TOC entry 3412 (class 2606 OID 32832)
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- TOC entry 3409 (class 2606 OID 32824)
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 3393 (class 2606 OID 32774)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 3472 (class 2606 OID 409613)
-- Name: note_edit_permissions note_edit_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_edit_permissions
    ADD CONSTRAINT note_edit_permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 3468 (class 2606 OID 41159)
-- Name: note_permissions note_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_permissions
    ADD CONSTRAINT note_permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 3399 (class 2606 OID 32792)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 3454 (class 2606 OID 40998)
-- Name: punishments punishments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.punishments
    ADD CONSTRAINT punishments_pkey PRIMARY KEY (id);


--
-- TOC entry 3418 (class 2606 OID 32855)
-- Name: roles roles_name_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_unique UNIQUE (name);


--
-- TOC entry 3420 (class 2606 OID 32853)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 3458 (class 2606 OID 41056)
-- Name: schedules schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_pkey PRIMARY KEY (id);


--
-- TOC entry 3402 (class 2606 OID 32799)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 3466 (class 2606 OID 41140)
-- Name: student_annual_averages student_annual_averages_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_annual_averages
    ADD CONSTRAINT student_annual_averages_pkey PRIMARY KEY (id);


--
-- TOC entry 3452 (class 2606 OID 33048)
-- Name: student_payments student_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_payments
    ADD CONSTRAINT student_payments_pkey PRIMARY KEY (id);


--
-- TOC entry 3464 (class 2606 OID 41123)
-- Name: student_trimestre_averages student_trimestre_averages_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_trimestre_averages
    ADD CONSTRAINT student_trimestre_averages_pkey PRIMARY KEY (id);


--
-- TOC entry 3432 (class 2606 OID 32912)
-- Name: students students_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_pkey PRIMARY KEY (id);


--
-- TOC entry 3434 (class 2606 OID 32914)
-- Name: students students_registration_number_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_registration_number_unique UNIQUE (registration_number);


--
-- TOC entry 3462 (class 2606 OID 41101)
-- Name: subject_averages subject_averages_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subject_averages
    ADD CONSTRAINT subject_averages_pkey PRIMARY KEY (id);


--
-- TOC entry 3430 (class 2606 OID 32903)
-- Name: subjects subjects_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_pkey PRIMARY KEY (id);


--
-- TOC entry 3442 (class 2606 OID 32981)
-- Name: teacher_invitations teacher_invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations
    ADD CONSTRAINT teacher_invitations_pkey PRIMARY KEY (id);


--
-- TOC entry 3444 (class 2606 OID 32993)
-- Name: teacher_invitations teacher_invitations_token_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations
    ADD CONSTRAINT teacher_invitations_token_unique UNIQUE (token);


--
-- TOC entry 3450 (class 2606 OID 33024)
-- Name: timetables timetables_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.timetables
    ADD CONSTRAINT timetables_pkey PRIMARY KEY (id);


--
-- TOC entry 3395 (class 2606 OID 32785)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 3397 (class 2606 OID 32783)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3389 (class 1259 OID 24588)
-- Name: users_sync_deleted_at_idx; Type: INDEX; Schema: neon_auth; Owner: neondb_owner
--

CREATE INDEX users_sync_deleted_at_idx ON neon_auth.users_sync USING btree (deleted_at);


--
-- TOC entry 3410 (class 1259 OID 32825)
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- TOC entry 3400 (class 1259 OID 32801)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 3403 (class 1259 OID 32800)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: neondb_owner
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 3521 (class 2606 OID 254024)
-- Name: cahier_de_texte cahier_de_texte_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte
    ADD CONSTRAINT cahier_de_texte_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3522 (class 2606 OID 254004)
-- Name: cahier_de_texte cahier_de_texte_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte
    ADD CONSTRAINT cahier_de_texte_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3523 (class 2606 OID 254009)
-- Name: cahier_de_texte cahier_de_texte_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte
    ADD CONSTRAINT cahier_de_texte_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3524 (class 2606 OID 254014)
-- Name: cahier_de_texte cahier_de_texte_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte
    ADD CONSTRAINT cahier_de_texte_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3525 (class 2606 OID 254019)
-- Name: cahier_de_texte cahier_de_texte_timetable_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.cahier_de_texte
    ADD CONSTRAINT cahier_de_texte_timetable_id_foreign FOREIGN KEY (timetable_id) REFERENCES public.timetables(id) ON DELETE CASCADE;


--
-- TOC entry 3490 (class 2606 OID 40965)
-- Name: class_teacher_subject class_teacher_subject_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject
    ADD CONSTRAINT class_teacher_subject_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3491 (class 2606 OID 33003)
-- Name: class_teacher_subject class_teacher_subject_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject
    ADD CONSTRAINT class_teacher_subject_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3492 (class 2606 OID 33013)
-- Name: class_teacher_subject class_teacher_subject_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject
    ADD CONSTRAINT class_teacher_subject_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3493 (class 2606 OID 33008)
-- Name: class_teacher_subject class_teacher_subject_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.class_teacher_subject
    ADD CONSTRAINT class_teacher_subject_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3474 (class 2606 OID 32887)
-- Name: classes classes_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3475 (class 2606 OID 32882)
-- Name: classes classes_entity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_entity_id_foreign FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- TOC entry 3476 (class 2606 OID 32892)
-- Name: classes classes_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 3503 (class 2606 OID 41029)
-- Name: conducts conducts_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.conducts
    ADD CONSTRAINT conducts_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3504 (class 2606 OID 41034)
-- Name: conducts conducts_entity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.conducts
    ADD CONSTRAINT conducts_entity_id_foreign FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- TOC entry 3505 (class 2606 OID 41024)
-- Name: conducts conducts_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.conducts
    ADD CONSTRAINT conducts_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3480 (class 2606 OID 32934)
-- Name: enrollments enrollments_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3481 (class 2606 OID 32929)
-- Name: enrollments enrollments_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3482 (class 2606 OID 32923)
-- Name: enrollments enrollments_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3509 (class 2606 OID 41090)
-- Name: grades grades_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grades
    ADD CONSTRAINT grades_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3510 (class 2606 OID 262144)
-- Name: grades grades_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grades
    ADD CONSTRAINT grades_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3511 (class 2606 OID 41080)
-- Name: grades grades_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grades
    ADD CONSTRAINT grades_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3512 (class 2606 OID 41085)
-- Name: grades grades_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.grades
    ADD CONSTRAINT grades_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3483 (class 2606 OID 32958)
-- Name: invitations invitations_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE SET NULL;


--
-- TOC entry 3484 (class 2606 OID 32948)
-- Name: invitations invitations_invited_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_invited_by_foreign FOREIGN KEY (invited_by) REFERENCES public.users(id);


--
-- TOC entry 3485 (class 2606 OID 32953)
-- Name: invitations invitations_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- TOC entry 3526 (class 2606 OID 409629)
-- Name: note_edit_permissions note_edit_permissions_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_edit_permissions
    ADD CONSTRAINT note_edit_permissions_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3527 (class 2606 OID 409619)
-- Name: note_edit_permissions note_edit_permissions_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_edit_permissions
    ADD CONSTRAINT note_edit_permissions_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3528 (class 2606 OID 409624)
-- Name: note_edit_permissions note_edit_permissions_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_edit_permissions
    ADD CONSTRAINT note_edit_permissions_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3529 (class 2606 OID 409614)
-- Name: note_edit_permissions note_edit_permissions_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_edit_permissions
    ADD CONSTRAINT note_edit_permissions_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3520 (class 2606 OID 41160)
-- Name: note_permissions note_permissions_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.note_permissions
    ADD CONSTRAINT note_permissions_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3500 (class 2606 OID 41004)
-- Name: punishments punishments_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.punishments
    ADD CONSTRAINT punishments_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3501 (class 2606 OID 41009)
-- Name: punishments punishments_entity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.punishments
    ADD CONSTRAINT punishments_entity_id_foreign FOREIGN KEY (entity_id) REFERENCES public.entities(id) ON DELETE CASCADE;


--
-- TOC entry 3502 (class 2606 OID 40999)
-- Name: punishments punishments_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.punishments
    ADD CONSTRAINT punishments_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3506 (class 2606 OID 41057)
-- Name: schedules schedules_classe_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_classe_id_foreign FOREIGN KEY (classe_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3507 (class 2606 OID 41067)
-- Name: schedules schedules_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3508 (class 2606 OID 41062)
-- Name: schedules schedules_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3518 (class 2606 OID 41146)
-- Name: student_annual_averages student_annual_averages_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_annual_averages
    ADD CONSTRAINT student_annual_averages_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3519 (class 2606 OID 41141)
-- Name: student_annual_averages student_annual_averages_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_annual_averages
    ADD CONSTRAINT student_annual_averages_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3498 (class 2606 OID 40970)
-- Name: student_payments student_payments_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_payments
    ADD CONSTRAINT student_payments_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3499 (class 2606 OID 33049)
-- Name: student_payments student_payments_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_payments
    ADD CONSTRAINT student_payments_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3516 (class 2606 OID 41129)
-- Name: student_trimestre_averages student_trimestre_averages_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_trimestre_averages
    ADD CONSTRAINT student_trimestre_averages_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3517 (class 2606 OID 41124)
-- Name: student_trimestre_averages student_trimestre_averages_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.student_trimestre_averages
    ADD CONSTRAINT student_trimestre_averages_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3479 (class 2606 OID 40980)
-- Name: students students_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3513 (class 2606 OID 41112)
-- Name: subject_averages subject_averages_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subject_averages
    ADD CONSTRAINT subject_averages_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3514 (class 2606 OID 41102)
-- Name: subject_averages subject_averages_student_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subject_averages
    ADD CONSTRAINT subject_averages_student_id_foreign FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3515 (class 2606 OID 41107)
-- Name: subject_averages subject_averages_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subject_averages
    ADD CONSTRAINT subject_averages_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3477 (class 2606 OID 40985)
-- Name: subjects subjects_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3478 (class 2606 OID 41045)
-- Name: subjects subjects_classe_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_classe_id_foreign FOREIGN KEY (classe_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3486 (class 2606 OID 40975)
-- Name: teacher_invitations teacher_invitations_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations
    ADD CONSTRAINT teacher_invitations_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3487 (class 2606 OID 32987)
-- Name: teacher_invitations teacher_invitations_censeur_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations
    ADD CONSTRAINT teacher_invitations_censeur_id_foreign FOREIGN KEY (censeur_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3488 (class 2606 OID 41040)
-- Name: teacher_invitations teacher_invitations_classe_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations
    ADD CONSTRAINT teacher_invitations_classe_id_foreign FOREIGN KEY (classe_id) REFERENCES public.classes(id) ON DELETE SET NULL;


--
-- TOC entry 3489 (class 2606 OID 32982)
-- Name: teacher_invitations teacher_invitations_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.teacher_invitations
    ADD CONSTRAINT teacher_invitations_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3494 (class 2606 OID 40960)
-- Name: timetables timetables_academic_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.timetables
    ADD CONSTRAINT timetables_academic_year_id_foreign FOREIGN KEY (academic_year_id) REFERENCES public.academic_years(id) ON DELETE CASCADE;


--
-- TOC entry 3495 (class 2606 OID 33025)
-- Name: timetables timetables_class_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.timetables
    ADD CONSTRAINT timetables_class_id_foreign FOREIGN KEY (class_id) REFERENCES public.classes(id) ON DELETE CASCADE;


--
-- TOC entry 3496 (class 2606 OID 33035)
-- Name: timetables timetables_subject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.timetables
    ADD CONSTRAINT timetables_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES public.subjects(id) ON DELETE CASCADE;


--
-- TOC entry 3497 (class 2606 OID 33030)
-- Name: timetables timetables_teacher_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.timetables
    ADD CONSTRAINT timetables_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 3473 (class 2606 OID 32965)
-- Name: users users_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: neondb_owner
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- TOC entry 2195 (class 826 OID 16394)
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE cloud_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO neon_superuser WITH GRANT OPTION;


--
-- TOC entry 2194 (class 826 OID 16393)
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: cloud_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE cloud_admin IN SCHEMA public GRANT ALL ON TABLES TO neon_superuser WITH GRANT OPTION;


-- Completed on 2026-01-01 09:42:33 WAT

--
-- PostgreSQL database dump complete
--

\unrestrict igpKSQ6U4dTDuPSaeD6oXrm5OHLQGXb4b4od1NCZa9L9T9a3PRXKIb95SappelW

