import streamlit as st
from langchain_core.messages import AIMessage, HumanMessage
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.runnables import RunnablePassthrough
from langchain_community.utilities import SQLDatabase
from langchain_core.output_parsers import StrOutputParser
from langchain_openai import ChatOpenAI
from dotenv import load_dotenv

# โหลด Environment Variables
load_dotenv()

# ฟังก์ชันเชื่อมต่อฐานข้อมูล MySQL
def init_database(user: str, password: str, host: str, port: str, database: str) -> SQLDatabase:
    db_uri = f"mysql+mysqlconnector://{user}:{password}@{host}:{port}/{database}"
    return SQLDatabase.from_uri(db_uri)

# ฟังก์ชันสร้าง SQL Query Chain
def get_sql_chain(db: SQLDatabase):
    template = """
    You are a data analyst at a company. You are interacting with a user who is asking you questions about the company's database.
    Based on the table schema below, write a SQL query that would answer the user's question. Take the conversation history into account.

    <SCHEMA>{schema}</SCHEMA>

    Conversation History: {chat_history}

    Write only the SQL query and nothing else. Do not wrap the SQL query in any other text, not even backticks.

    Question: {question}
    SQL Query:
    """
    prompt = ChatPromptTemplate.from_template(template)

    llm = ChatOpenAI(temperature=0)

    def get_schema(_):
        return db.get_table_info()

    return (
        RunnablePassthrough.assign(schema=get_schema)
        | prompt
        | llm.bind(stop="\nSQL Result:")
        | StrOutputParser()
    )

# ฟังก์ชันสำหรับสร้างคำตอบจากผลลัพธ์ SQL
def get_response(user_query: str, db: SQLDatabase, chat_history: list):
    sql_chain = get_sql_chain(db)

    # สร้าง prompt สำหรับการตอบคำถาม
    template = """
    Based on the table schema below, question, sql query, and sql response, write a natural language response:
    <SCHEMA>{schema}</SCHEMA>
    Conversation History: {chat_history}
    Question: {question}
    SQL Query: {query}
    SQL Response: {response}
    """
    prompt = ChatPromptTemplate.from_template(template)

    llm = ChatOpenAI(temperature=0)

    def get_schema(_):
        return db.get_table_info()

    chain = (
        RunnablePassthrough.assign(query=sql_chain).assign(
            schema=get_schema,
            response=lambda vars: db.run(vars["query"]),
        )
        | prompt
        | llm
        | StrOutputParser()
    )

    return chain.invoke({
        "question": user_query,
        "chat_history": chat_history,
    })

# การตั้งค่าหน้า Streamlit
st.set_page_config(page_title="Chat AI จาก Tucoop (MySQL)", page_icon=":speech_balloon:", initial_sidebar_state="expanded")

# Session State Initialization
if "chat_history" not in st.session_state:
    st.session_state.chat_history = [
        AIMessage(content="Hello! I'm a chatbot that can help you with your SQL queries. Ask me anything about your database!")
    ]

if "db" not in st.session_state:
    st.session_state.db = None

st.title("Chat กับสหกรณ์")

# Sidebar สำหรับการตั้งค่าการเชื่อมต่อฐานข้อมูล
with st.sidebar:
    st.title("Database Connection")
    host = st.text_input("Host", value="203.131.211.13", key="Host")
    port = st.text_input("Port", value="3306", key="Port")
    user = st.text_input("User", value="chatbot", key="User")
    password = st.text_input("Password", type="password", value="password", key="Password")
    database = st.text_input("Database", value="chatbot", key="Database")

    if st.button("Connect"):
        with st.spinner("Connecting to the database..."):
            # แสดง Connection String
            connection_string = f"mysql+mysqlconnector://{user}:{password}@{host}:{port}/{database}"
            st.text(f"Connection String: {connection_string}")
            try:
                # ลองสร้างการเชื่อมต่อ
                db = init_database(user, password, host, port, database)
                st.session_state.db = db
                st.success("Connected to the database!")
            except Exception as e:
                # แสดงข้อความ error และ connection string
                st.error(f"Failed to connect to database: {e}")
                st.text(f"Connection String: {connection_string}")

# รับคำถามจากผู้ใช้
user_query = st.chat_input("Type your SQL-related question here...")

# แสดงประวัติการสนทนา
for message in st.session_state.chat_history:
    if isinstance(message, AIMessage):
        with st.chat_message("AI"):
            st.markdown(message.content)
    elif isinstance(message, HumanMessage):
        with st.chat_message("Human"):
            st.markdown(message.content)

# หากผู้ใช้ส่งคำถาม
if user_query:
    st.session_state.chat_history.append(HumanMessage(content=user_query))
    with st.chat_message("Human"):
        st.markdown(user_query)

    if st.session_state.db:
        try:
            with st.spinner("Thinking..."):
                response = get_response(user_query, st.session_state.db, st.session_state.chat_history)
            st.session_state.chat_history.append(AIMessage(content=response))
            with st.chat_message("AI"):
                st.markdown(response)
        except Exception as e:
            with st.chat_message("AI"):
                st.error(f"Error: {e}")
    else:
        with st.chat_message("AI"):
            st.error("Please connect to a database first!")